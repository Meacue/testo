<?php

declare(strict_types=1);

namespace Testo\Assert\DataType;

use Testo\Assert\State\AssertException;
use Testo\Assert\StaticState;

/**
 * Assertion utilities for integer data type.
 */
class AssertInt
{
    public function __construct(
        public int $value,
    ) {}

    /**
     * Asserts that the integer value is greater than given value.
     *
     * @param int $min Minimum threshold to compare with.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function greaterThan(int $min, string $message = ''): void
    {
        if ($this->value > $min) {
            StaticState::log('Assert int greater than `' . $min . '`', $message);
            return;
        }

        StaticState::fail(AssertException::compare(
            $min,
            $this->value,
            $message,
            pattern: 'Failed asserting that value `%2$s` is greater than `%1$s`.',
            showDiff: false,
        ));
    }

    /**
     * Asserts that the integer value is greater than or equal to given value.
     *
     * @param int $min Minimum threshold to compare with.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function greaterThanOrEqual(int $min, string $message = ''): void
    {
        if ($this->value >= $min) {
            StaticState::log('Assert int greater than or equal to `' . $min . '`', $message);
            return;
        }

        StaticState::fail(AssertException::compare(
            $min,
            $this->value,
            $message,
            pattern: 'Failed asserting that value `%2$s` is greater than or equal to `%1$s`.',
            showDiff: false,
        ));
    }
}
