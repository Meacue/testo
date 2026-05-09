<?php

declare(strict_types=1);

namespace Testo\Assert\State\Assertion;

/**
 * Assertion exception for value comparisons.
 *
 * Carries the raw `expected` and `actual` values alongside the textual
 * assertion record fields so output adapters can render them as needed
 * (e.g. terminal diff, TeamCity `comparisonFailure` message).
 */
class ComparisonFailure extends AssertionException
{
    /**
     * @param non-empty-string $value Stringified actual value used in the assertion message.
     * @param non-empty-string $assertion The assertion result.
     * @param string $context Optional user-provided context describing what is being asserted.
     * @param string $reason The reason for the assertion failure.
     * @param string $details The detailed assertion failure information (diff).
     */
    public function __construct(
        public readonly mixed $expected,
        public readonly mixed $actual,
        string $value,
        string $assertion,
        string $context,
        string $reason,
        string $details = '',
    ) {
        parent::__construct(
            value: $value,
            assertion: $assertion,
            context: $context,
            reason: $reason,
            details: $details,
        );
    }
}
