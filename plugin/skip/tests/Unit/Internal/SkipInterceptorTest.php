<?php

declare(strict_types=1);

namespace Tests\Skip\Unit\Internal;

use Internal\Path;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\Identity\SuiteIdentity;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Definition\CaseDefinition;
use Testo\Core\Definition\TestDefinition;
use Testo\Core\Definition\TestDefinitions;
use Testo\Core\Exception\SkipTest;
use Testo\Core\Value\Status;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Policy\ConflictPolicy;
use Testo\Test;
use Testo\Skip\Internal\SkipInterceptor;
use Testo\Skip;
use Tests\Skip\Unit\Fixture\SkipMixedMethodsFixture;

/**
 * @see SkipInterceptor
 */
#[Test]
#[Covers(SkipInterceptor::class)]
final class SkipInterceptorTest
{
    /**
     * The interceptor short-circuits: `$next` (and with it every inner interceptor, hook and the
     * body) is never reached.
     */
    public function returnsSkippedResultWithoutCallingNext(): void
    {
        $interceptor = new SkipInterceptor(new Skip('broken by the pricing rework, see ISSUE-123'));
        $nextCalled = false;

        $result = $interceptor->runTest(
            self::createTestInfo('skipped'),
            static function (TestInfo $info) use (&$nextCalled): TestResult {
                $nextCalled = true;
                return new TestResult(info: $info, status: Status::Passed);
            },
        );

        Assert::false($nextCalled);
        Assert::same($result->status, Status::Skipped);
        Assert::instanceOf($result->failure, SkipTest::class);
        Assert::same($result->summary->count(Status::Skipped), 1);
    }

    public function composesReasonAfterGeneratedPart(): void
    {
        $interceptor = new SkipInterceptor(new Skip('broken by the pricing rework, see ISSUE-123'));

        $result = $interceptor->runTest(self::createTestInfo('skipped'), self::unreachableNext());

        Assert::same(
            $result->failure?->getMessage(),
            SkipMixedMethodsFixture::class
            . '::skipped is skipped via #[Skip] ==> broken by the pricing rework, see ISSUE-123',
        );
    }

    /**
     * An empty reason falls back to the generated part alone — no reporter ever shows an
     * empty skip message.
     */
    public function fallsBackToGeneratedMessageWithoutReason(): void
    {
        $interceptor = new SkipInterceptor(new Skip());

        $result = $interceptor->runTest(self::createTestInfo('skippedNoReason'), self::unreachableNext());

        Assert::same(
            $result->failure?->getMessage(),
            SkipMixedMethodsFixture::class . '::skippedNoReason is skipped via #[Skip]',
        );
    }

    /**
     * The terminal renders a test's PHPDoc description from the result attributes (as the
     * regular test path stamps it), so the skipped result must carry it too.
     */
    public function carriesDescriptionInResult(): void
    {
        $interceptor = new SkipInterceptor(new Skip('any'));

        $result = $interceptor->runTest(self::createTestInfo('skipped'), self::unreachableNext());

        Assert::same($result->attributes['description'], 'Checks that order totals include the reworked pricing.');
        Assert::same($result->attributes['duration'], 0);
    }

    /**
     * `#[Skip]` is a plain-test feature: without this declaration the attribute would skip a
     * `#[Bench]` or `#[TestInline]` target too.
     */
    public function declaresTestTypeScopingSkipToPlainTests(): void
    {
        $attributes = (new \ReflectionClass(SkipInterceptor::class))
            ->getAttributes(InterceptorOptions::class);

        Assert::count($attributes, 1);
        Assert::same($attributes[0]->newInstance()->testType, TestType::Test);
    }

    /**
     * The rest of the placement contract: the slot sits outer to the fiber wrap and the data
     * provider, and `ConflictPolicy::Last` is what lets the method-level attribute (listed after
     * the class-level one) win.
     */
    public function declaresOrderAndConflictPolicy(): void
    {
        $attributes = (new \ReflectionClass(SkipInterceptor::class))
            ->getAttributes(InterceptorOptions::class);

        Assert::count($attributes, 1);
        $options = $attributes[0]->newInstance();
        Assert::true($options->order < InterceptorOptions::ORDER_DATA_PROVIDER - 1);
        Assert::true($options->order > InterceptorOptions::ORDER_FILTER);
        Assert::same($options->onConflict, ConflictPolicy::Last);
    }

    /**
     * @param non-empty-string $method
     */
    private static function createTestInfo(string $method): TestInfo
    {
        $definition = new TestDefinition(new \ReflectionMethod(SkipMixedMethodsFixture::class, $method));
        $caseDefinition = new CaseDefinition(
            name: SkipMixedMethodsFixture::class,
            type: 'test',
            file: Path::create(__FILE__),
            reflection: new \ReflectionClass(SkipMixedMethodsFixture::class),
            tests: TestDefinitions::fromArray(...[$method => $definition]),
        );
        $caseInfo = new CaseInfo(definition: $caseDefinition, suiteIdentity: new SuiteIdentity('Test/Unit'));

        return new TestInfo(name: $method, caseInfo: $caseInfo, testDefinition: $definition);
    }

    private static function unreachableNext(): \Closure
    {
        return static fn(TestInfo $info): TestResult => throw new \LogicException('Must never be reached.');
    }
}
