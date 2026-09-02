<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Core\Exception\SkipTest;
use Testo\Core\Value\Status;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Test\Internal\SkipInterceptor;

/**
 * Marks a test as skipped without deleting or hiding it.
 *
 * The test is not executed, but stays visible in every report as {@see Status::Skipped}
 * with a composed reason, so parked tests are counted and reviewable instead of silently
 * rotting. Contrast with a group filter (`#[Group('x')]` + `--group=!x`), which makes the
 * test disappear from reports entirely: a group is "a category I sometimes don't run"
 * (decided by the runner, invisible), while `#[Skip]` is "this test is parked and must be
 * returned to" (decided in code, always visible, with a reason).
 *
 * Behavior depends on the target:
 *
 * On a method or function — only that test is skipped:
 *
 * ```
 *  #[Test]
 *  final class OrderTest
 *  {
 *      #[Skip('broken by the pricing rework, see ISSUE-123')]
 *      public function calculatesTotal(): void { ... }  // reported as Skipped, never runs
 *
 *      public function createsOrder(): void { ... }     // runs as usual
 *  }
 * ```
 *
 * On a class — every test of the case is skipped. The attribute is inherited from parent
 * classes and traits (like `#[Group]`); a method-level `#[Skip]` reason wins over the
 * class-level one.
 *
 * The reported failure message is composed as `{testId} is skipped via #[Skip]`, extended
 * with ` ==> {reason}` when a reason is given — so JUnit/TeamCity/HTML output always shows
 * the origin of the skip, even with an empty reason.
 *
 * Runtime contract (v1):
 *
 * - The skipped test never enters the per-test pipeline: `#[BeforeTest]`/`#[AfterTest]`
 *   hooks, data providers, `#[Retry]`/`#[Repeat]`, fibers and coverage never engage.
 *   A data-driven test yields a single Skipped entry (providers are not expanded).
 * - `#[BeforeClass]`/`#[AfterClass]` hooks still run — also when every test of the case
 *   is skipped. Full case suppression is a possible follow-up.
 * - The case class is not instantiated, unless a non-static class-level hook forces
 *   construction (class-level hooks may be non-static; that builds the class).
 * - A run consisting only of `#[Skip]`-marked tests is successful (exit code 0):
 *   Skipped is neither a success nor a failure.
 * - On a non-test method the attribute is inert (like `#[Group]` on a helper).
 *
 * The attribute is handled by {@see SkipInterceptor} — registered by {@see TestPlugin}
 * and declared as the {@see FallbackInterceptor} for standalone use. The interceptor does
 * its own lookup over the case's tests (a case-level fallback would not see a method-level
 * attribute), so the registered instance covers every target; the fallback spawn is
 * deduplicated by the pipeline's conflict policy.
 * For skipping at runtime — from the test body, based on the environment — throw
 * {@see SkipTest} instead.
 *
 * @see SkipTest for the runtime counterpart and the `is skipped via #[Skip]` marker
 *      distinguishing declarative skips in reports.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
#[FallbackInterceptor(SkipInterceptor::class)]
final readonly class Skip implements Interceptable
{
    /**
     * @param string $reason Why the test is parked. Optional, but a reference to an issue
     *        (`'flaky on CI, see ISSUE-123'`) keeps the skip reviewable.
     */
    public function __construct(
        public string $reason = '',
    ) {}
}
