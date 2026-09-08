<?php

declare(strict_types=1);

namespace Tests\Test\Unit\Fixture;

use Testo\Test\Skip;

/**
 * Fixture mixing skipped and enabled tests.
 *
 * Used by {@see \Tests\Test\Unit\Internal\SkipInterceptorTest}: one test is skipped with a reason,
 * one without a reason, and one stays enabled to show what the interceptor leaves alone.
 */
final class SkipMixedMethodsFixture
{
    /**
     * Checks that order totals include the reworked pricing.
     */
    #[Skip('broken by the pricing rework, see ISSUE-123')]
    public function skipped(): void {}

    #[Skip]
    public function skippedNoReason(): void {}

    public function enabled(): void {}
}
