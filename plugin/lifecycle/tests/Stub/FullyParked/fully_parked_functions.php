<?php

declare(strict_types=1);

namespace Tests\Lifecycle\Stub\FullyParked;

use Testo\Lifecycle\AfterClass;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeClass;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;
use Testo\Test\Skip;

/**
 * A fully parked function-based case: every `#[Test]` function is under `#[Skip]`. Mirrors
 * {@see FullyParkedClassStub} for the function-based shape of the same scenario. Its two tests
 * spell the attribute both ways — `parkedFnOne` with a reason, `parkedFnTwo` without — so neither
 * form leaves the case with an active test.
 *
 * The `#[Skip]` case interceptor deactivates the skipped tests — they leave the case's active
 * test set — before the {@see \Testo\Lifecycle\Internal\LifecycleInterceptor} runs, so hook
 * discovery must not depend on the surviving tests: `#[BeforeClass]`/`#[AfterClass]` still run
 * for the case (the `#[Skip]` contract), while the per-test hooks have nothing to wrap.
 *
 * Static hook counters accumulate across catalog runs — feature tests assert deltas.
 * State is shared through {@see FullyParkedFunctionState} because functions have no `$this`.
 */
#[BeforeClass]
function parkedCaseSetUpClass(): void
{
    ++FullyParkedFunctionState::$beforeClassCalls;
}

#[AfterClass]
function parkedCaseTearDownClass(): void
{
    ++FullyParkedFunctionState::$afterClassCalls;
}

#[BeforeTest]
function parkedCaseSetUp(): void
{
    ++FullyParkedFunctionState::$beforeTestCalls;
}

#[AfterTest]
function parkedCaseTearDown(): void
{
    ++FullyParkedFunctionState::$afterTestCalls;
}

#[Test]
#[Skip('the whole functional case is parked')]
function parkedFnOne(): void
{
    throw new \LogicException('Must never run: the test is parked.');
}

#[Test]
#[Skip]
function parkedFnTwo(): void
{
    throw new \LogicException('Must never run: the test is parked.');
}

/**
 * Call counters for the lifecycle functions above. Not autoloadable — the feature test
 * `require_once`s this file before touching the counters.
 */
final class FullyParkedFunctionState
{
    public static int $beforeClassCalls = 0;
    public static int $afterClassCalls = 0;
    public static int $beforeTestCalls = 0;
    public static int $afterTestCalls = 0;
}
