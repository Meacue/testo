<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Feature;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Lifecycle\Stub\FullyParked\FullyParkedClassStub;
use Tests\Lifecycle\Stub\FullyParked\FullyParkedFunctionState;

/**
 * End-to-end regression test for {@see LifecycleInterceptor}: the `#[BeforeClass]`/`#[AfterClass]` hooks
 * of a case still run when an outer case interceptor — here `#[Skip]` from `testo/test` — leaves
 * the case without a single active test.
 *
 * The `#[Skip]` case interceptor deactivates the parked tests before the {@see LifecycleInterceptor}
 * collects the case's hooks, so hook discovery must not depend on the surviving tests. It does not:
 * the hooks are the case's non-tests. Prefilling defines every member as a non-test,
 * {@see LifecycleInterceptor} demotes back the ones a finder took for tests (a class-level `#[Test]`
 * promotes the hook methods of a class case first), and it then reads them all back with
 * `filter(isTest: false)` — non-tests outlive the deactivation of the tests.
 *
 * Both case shapes are pinned here through the real pipeline. Their members are prefilled by
 * {@see \Testo\Core\Definition\CaseDefinitions::define()} from the two sources it knows: the
 * methods of {@see \Testo\Core\Definition\CaseDefinition::$reflection} for a class-based case,
 * the file's free functions for a function-based one.
 */
#[Test]
#[Covers(LifecycleInterceptor::class)]
#[TestingSuite(path: __DIR__ . '/../Stub/FullyParked')]
final class FullyParkedCaseFeatureTest
{
    public function __construct()
    {
        # Functions are not autoloadable: load the stub so TestRunner::runTest() can resolve the
        # function names below. The pipeline re-includes the same file (include_once) when it runs.
        require_once __DIR__ . '/../Stub/FullyParked/fully_parked_functions.php';
    }

    /**
     * The function-based case shape: class-level hooks fire exactly once per catalog run even
     * though no test of the case stays active; per-test hooks have nothing to wrap and stay silent.
     */
    public function classHooksRunForFullyParkedFunctionCase(): void
    {
        $beforeClass = FullyParkedFunctionState::$beforeClassCalls;
        $afterClass = FullyParkedFunctionState::$afterClassCalls;
        $beforeTest = FullyParkedFunctionState::$beforeTestCalls;
        $afterTest = FullyParkedFunctionState::$afterTestCalls;

        $result = TestRunner::runTest('Tests\Lifecycle\Stub\FullyParked\parkedFnOne');

        Assert::same($result->status, Status::Skipped);
        Assert::same(FullyParkedFunctionState::$beforeClassCalls - $beforeClass, 1);
        Assert::same(FullyParkedFunctionState::$afterClassCalls - $afterClass, 1);
        # No test of the case ran, so the per-test hooks never fired.
        Assert::same(FullyParkedFunctionState::$beforeTestCalls - $beforeTest, 0);
        Assert::same(FullyParkedFunctionState::$afterTestCalls - $afterTest, 0);
    }

    /**
     * The class-based analog: hooks are the non-tests prefilled from the case's class reflection
     * and must keep firing for a fully parked class exactly as before.
     */
    public function classHooksRunForFullyParkedClassCase(): void
    {
        $beforeClass = FullyParkedClassStub::$beforeClassCalls;
        $afterClass = FullyParkedClassStub::$afterClassCalls;

        $result = TestRunner::runTest([FullyParkedClassStub::class, 'parked']);

        Assert::same($result->status, Status::Skipped);
        Assert::same(FullyParkedClassStub::$beforeClassCalls - $beforeClass, 1);
        Assert::same(FullyParkedClassStub::$afterClassCalls - $afterClass, 1);
    }
}
