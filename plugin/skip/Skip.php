<?php

declare(strict_types=1);

namespace Testo;

use Testo\Core\Definition\TestDefinition;
use Testo\Core\Exception\SkipTest;
use Testo\Core\Value\Status;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Skip\Internal\SkipInterceptor;
use Testo\Skip\SkipPlugin;

/**
 * Marks a test as skipped without deleting or hiding it.
 *
 * The test is not executed, but stays in the results as {@see Status::Skipped}: it is counted in
 * the totals and carries its reason in the result's failure message. Contrast with a filter, which
 * drops the test from the run and from the results entirely.
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
 * On a class every test of the case is skipped. The attribute is inherited from parent classes,
 * traits and overridden methods; a method-level `#[Skip]` wins over the class-level one, reason
 * included.
 *
 * The failure message reads `{testId} is skipped via #[Skip]`, extended with ` ==> {reason}` when
 * a reason is given. The JUnit, TeamCity and HTML reporters show it; the terminal does not.
 *
 * Runtime contract:
 *
 * - The test is flagged {@see TestDefinition::$skipped} before the run and reported at the entry
 *   of its own pipeline, so nothing that prepares, wraps or multiplies a test body engages for it.
 *   What each of those does for a skipped test is its own to document.
 * - A run consisting only of skipped tests is successful (exit code 0).
 * - The attribute is inert on a non-test member and on a case of any type but
 *   {@see TestType::Test}.
 *
 * No registration is needed: the attribute wires {@see SkipInterceptor} itself, from a class, a
 * method or a function alike. {@see SkipPlugin}, part of the default suite plugins, is what sets
 * the flag ahead of the run.
 *
 * For skipping at runtime — from the test body, based on the environment — throw {@see SkipTest}
 * instead. It reaches the same {@see Status::Skipped}, but only once the body has started, so a
 * flagged test and a thrown skip are not interchangeable; the `is skipped via #[Skip]` marker
 * tells the two apart in reports.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
#[FallbackInterceptor(SkipInterceptor::class)]
final readonly class Skip implements Interceptable
{
    /**
     * @param string $reason Why the test is skipped. A reference to an issue
     *        (`'flaky on CI, see ISSUE-123'`) keeps the skip reviewable.
     */
    public function __construct(
        public string $reason = '',
    ) {}
}
