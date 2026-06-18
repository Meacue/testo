<?php

declare(strict_types=1);

namespace Tests\Assert\Feature;

use Testo\Assert;
use Testo\Assert\Internal\Assertion\AssertJson as AssertJsonImpl;
use Testo\Assert\State\Assertion;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Assert\Stub\AssertJsonNegative;

/**
 * Failure rendering of {@see Assert::json()} assertions, observed through the runner.
 *
 * @see Assert::json()
 */
#[Test]
#[Covers(Assert::class, 'json')]
#[Covers(AssertJsonImpl::class)]
#[TestingSuite(path: __DIR__ . '/../Stub')]
final class AssertJsonTest
{
    public function invalidJson(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'invalidJson']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('got');
    }

    public function isObjectOnArray(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'isObjectOnArray']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('got array');
    }

    public function isArrayOnObject(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'isArrayOnObject']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('got object');
    }

    public function isPrimitiveOnObject(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'isPrimitiveOnObject']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('got object');
    }

    public function emptyOnNonEmpty(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'emptyOnNonEmpty']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('3 element');
    }

    public function wrongCount(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'wrongCount']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('got 3');
    }

    public function missingKeys(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'missingKeys']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('missing key')
            ->contains('`name`')
            ->contains('`email`');
    }

    public function exceedsMaxDepth(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'exceedsMaxDepth']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('actual depth is 3');
    }

    public function wrongMatchesType(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'wrongMatchesType']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('got 42');
    }

    public function isStructureOnPrimitive(): void
    {
        $result = TestRunner::runTest([AssertJsonNegative::class, 'isStructureOnPrimitive']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('got int');
    }
}
