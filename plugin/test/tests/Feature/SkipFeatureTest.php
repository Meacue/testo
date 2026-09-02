<?php

declare(strict_types=1);

namespace Tests\Test\Feature;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Exception\SkipTest;
use Testo\Core\Value\Status;
use Testo\Data\MultipleResult;
use Testo\Test;
use Testo\Test\Internal\SkipInterceptor;
use Testo\Test\Skip;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Test\Stub\Skip\SkipChildStub;
use Tests\Test\Stub\Skip\SkipClassAndMethodStub;
use Tests\Test\Stub\Skip\SkipClassLevelStub;
use Tests\Test\Stub\Skip\SkipConstructorSpyStub;
use Tests\Test\Stub\Skip\SkipInFiberStub;
use Tests\Test\Stub\Skip\SkipMethodStub;
use Tests\Test\Stub\Skip\SkipNonStaticHookStub;
use Tests\Test\Stub\Skip\SkipTraitStub;
use Tests\Test\Stub\Skip\SkipWithDataProviderStub;
use Tests\Test\Stub\Skip\SkipWithHooksStub;
use Tests\Test\Stub\Skip\SkipWithRepeatStub;
use Tests\Test\Stub\Skip\SkipWithRetryStub;

#[Test]
#[TestingSuite(path: __DIR__ . '/../Stub/Skip')]
#[Covers(Skip::class)]
#[Covers(SkipInterceptor::class)]
final class SkipFeatureTest
{
    public function methodLevelSkipReportsSkippedWithComposedReason(): void
    {
        $result = TestRunner::runTest([SkipMethodStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::true($result->failure instanceof SkipTest);
        Assert::same(
            $result->failure->getMessage(),
            SkipMethodStub::class . '::parked is skipped via #[Skip] ==> broken by the pricing rework, see ISSUE-123',
        );
    }

    public function emptyReasonFallsBackToGeneratedMessage(): void
    {
        $result = TestRunner::runTest([SkipMethodStub::class, 'parkedNoReason']);

        Assert::same($result->status, Status::Skipped);
        Assert::same(
            $result->failure?->getMessage(),
            SkipMethodStub::class . '::parkedNoReason is skipped via #[Skip]',
        );
    }

    public function controlNeighborNextToParkedTestsStillRuns(): void
    {
        $result = TestRunner::runTest([SkipMethodStub::class, 'enabled']);

        Assert::same($result->status, Status::Passed);
    }

    public function classLevelSkipParksEveryTestWithClassReason(): void
    {
        $first = TestRunner::runTest([SkipClassLevelStub::class, 'firstParked']);
        $second = TestRunner::runTest([SkipClassLevelStub::class, 'secondParked']);

        Assert::same($first->status, Status::Skipped);
        Assert::same($second->status, Status::Skipped);
        Assert::true(\str_ends_with((string) $first->failure?->getMessage(), ' ==> the whole case is parked'));
        Assert::true(\str_ends_with((string) $second->failure?->getMessage(), ' ==> the whole case is parked'));
    }

    public function methodReasonWinsOverClassReason(): void
    {
        $own = TestRunner::runTest([SkipClassAndMethodStub::class, 'ownReason']);
        $inherited = TestRunner::runTest([SkipClassAndMethodStub::class, 'classReason']);

        Assert::true(\str_ends_with((string) $own->failure?->getMessage(), ' ==> method-specific reason'));
        Assert::true(\str_ends_with((string) $inherited->failure?->getMessage(), ' ==> class-wide reason'));
    }

    public function functionalTestUsesFunctionFqnInMessage(): void
    {
        $result = TestRunner::runTest('Tests\Test\Stub\Skip\parked_function');

        Assert::same($result->status, Status::Skipped);
        Assert::same(
            $result->failure?->getMessage(),
            'Tests\Test\Stub\Skip\parked_function is skipped via #[Skip] ==> functional test is parked',
        );
    }

    /**
     * The origin contract for downstream consumers: a `#[Skip]`-parked result carries the
     * attribute instances in `$result->info`, unlike a runtime `throw SkipTest` skip.
     */
    public function parkedResultCarriesOriginAttribute(): void
    {
        $result = TestRunner::runTest([SkipMethodStub::class, 'parked']);

        $origin = $result->info->getAttribute(Skip::class);
        Assert::true(\is_array($origin) && $origin !== []);
        Assert::true($origin[0] instanceof Skip);
    }

    /**
     * The parked test is filtered out before the case runs: class-level hooks fire as usual
     * (once per catalog run), per-test hooks fire only for the enabled control test.
     */
    public function classHooksRunButTestHooksDoNot(): void
    {
        $beforeClass = SkipWithHooksStub::$beforeClass;
        $afterClass = SkipWithHooksStub::$afterClass;
        $beforeTest = SkipWithHooksStub::$beforeTest;
        $afterTest = SkipWithHooksStub::$afterTest;

        $result = TestRunner::runTest([SkipWithHooksStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::same(SkipWithHooksStub::$beforeClass - $beforeClass, 1);
        Assert::same(SkipWithHooksStub::$afterClass - $afterClass, 1);
        # Only the enabled control test of the case went through the per-test pipeline.
        Assert::same(SkipWithHooksStub::$beforeTest - $beforeTest, 1);
        Assert::same(SkipWithHooksStub::$afterTest - $afterTest, 1);
    }

    public function fullyParkedCaseWithoutHooksIsNeverInstantiated(): void
    {
        $result = TestRunner::runTest([SkipConstructorSpyStub::class, 'firstParked']);

        Assert::same($result->status, Status::Skipped);
        Assert::false(SkipConstructorSpyStub::$constructed);
    }

    /**
     * Documented caveat: a non-static class-level hook builds the class even when every
     * test is parked — pinned so a future change is conscious, not accidental.
     */
    public function nonStaticClassHookStillBuildsTheClass(): void
    {
        $result = TestRunner::runTest([SkipNonStaticHookStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::true(SkipNonStaticHookStub::$constructed);
    }

    public function classLevelSkipIsInheritedFromParent(): void
    {
        $result = TestRunner::runTest([SkipChildStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::true(\str_ends_with((string) $result->failure?->getMessage(), ' ==> inherited from the parent class'));
    }

    public function classLevelSkipIsInheritedFromTrait(): void
    {
        $result = TestRunner::runTest([SkipTraitStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::true(\str_ends_with((string) $result->failure?->getMessage(), ' ==> inherited from the trait'));
    }

    /**
     * A data-driven parked test yields a single Skipped node: providers are not expanded
     * (and not even called), no `MultipleResult` aggregate is attached.
     */
    public function dataProviderIsNotExpandedForParkedTest(): void
    {
        $result = TestRunner::runTest([SkipWithDataProviderStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::null($result->getAttribute(MultipleResult::class));
        Assert::false(SkipWithDataProviderStub::$providerCalled);
    }

    public function retryDoesNotEngageForParkedTest(): void
    {
        $attempts = SkipWithRetryStub::$attempts;

        $result = TestRunner::runTest([SkipWithRetryStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::same(SkipWithRetryStub::$attempts - $attempts, 0);
    }

    public function repeatDoesNotEngageForParkedTest(): void
    {
        $result = TestRunner::runTest([SkipWithRepeatStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::false(SkipWithRepeatStub::$bodyRan);
    }

    /**
     * Fiber compatibility: the skip interceptor wraps the fiber batch runner instead of
     * replacing it — the enabled test still runs on the scheduler, the parked one is skipped.
     */
    public function fiberBatchRunnerSurvivesTheWrap(): void
    {
        $enabled = TestRunner::runTest([SkipInFiberStub::class, 'enabled']);
        $parked = TestRunner::runTest([SkipInFiberStub::class, 'parked']);

        Assert::same($enabled->status, Status::Passed);
        Assert::same($parked->status, Status::Skipped);
    }
}
