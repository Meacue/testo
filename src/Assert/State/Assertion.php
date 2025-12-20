<?php

declare(strict_types=1);

namespace Testo\Assert\State;

/**
 *
 * Expected format for failed assertions:
 *
 * ```
 *  Failed that <value> <assertion>: <wentWrong>.
 *  <details>
 * ```
 *
 * Expected format for successful assertions:
 * ```
 *  Assert that <value> <assertion>.
 * ```
 */
interface Assertion extends Record
{
    /**
     * Get the value that was asserted.
     *
     * @return non-empty-string
     */
    public function getValue(): string;

    /**
     * Get the assertion that was performed.
     *
     * Examples:
     *  - is greater than 10
     *  - contains key 'username'
     *  - has count of 5
     *  - is instance of `DateTimeInterface`
     *
     * @return non-empty-string
     */
    public function getAssertion(): string;

    /**
     * Get the reason why the assertion failed.
     *
     * Examples:
     *  - expected exactly 42, got 43
     *  - key 'username' not found
     *  - 42 is not greater than 100
     */
    public function getFailReason(): string;

    /**
     * Get detailed information about the assertion failure.
     *
     * For example, a diff between expected and actual values.
     */
    public function getFailDetails(): string;
}
