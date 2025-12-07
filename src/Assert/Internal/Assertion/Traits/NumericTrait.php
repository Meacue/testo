<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion\Traits;

use Testo\Assert\State\AssertException;
use Testo\Assert\State\AssertTypeSuccess;

/**
 * Contains methods for comparing numeric values
 * @property int|float $value
 * @property AssertTypeSuccess $parent
 */
trait NumericTrait
{
    /**
     * Asserts that numeric value is greater than the given minimum.
     *
     * @param int|float $min Minimum threshold to compare with.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function greaterThan(int|float $min, string $message = ''): self
    {
        if ($this->value > $min) {
            $this->parent->log('Assert `' . $this->value . ' > ' . $min . '`', $message);
            return $this;
        }

        $this->parent->fail(AssertException::compare(
            $min,
            $this->value,
            $message,
            pattern: 'Failed asserting that `%2$s > %1$s`.',
            showDiff: false,
        ));
    }

    /**
     * Asserts that the numeric value is greater than or equal to given minimum.
     *
     * @param int|float $min Minimum threshold to compare with.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function greaterThanOrEqual(int|float $min, string $message = ''): self
    {
        if ($this->value >= $min) {
            $this->parent->log('Assert `' . $this->value . ' >= ' . $min . '`', $message);
            return $this;
        }

        $this->parent->fail(AssertException::compare(
            $min,
            $this->value,
            $message,
            pattern: 'Failed asserting that `%2$s >= %1$s`.',
            showDiff: false,
        ));
    }

    /**
     * Asserts that numeric value is less than the given maximum.
     *
     * @param int|float $max Maximum threshold to compare with.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function lessThan(int|float $max, string $message = ''): self
    {
        if ($this->value < $max) {
            $this->parent->log('Assert `' . $this->value . ' < ' . $max . '`', $message);
            return $this;
        }

        $this->parent->fail(AssertException::compare(
            $max,
            $this->value,
            $message,
            pattern: 'Failed asserting that `%2$s < %1$s`.',
            showDiff: false,
        ));
    }

    /**
     * Asserts that the numeric value is less than or equal to given maximum.
     *
     * @param int|float $max Maximum threshold to compare with.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function lessThanOrEqual(int|float $max, string $message = ''): self
    {
        if ($this->value <= $max) {
            $this->parent->log('Assert `' . $this->value . ' <= ' . $max . '`', $message);
            return $this;
        }

        $this->parent->fail(AssertException::compare(
            $max,
            $this->value,
            $message,
            pattern: 'Failed asserting that `%2$s <= %1$s`.',
            showDiff: false,
        ));
    }
}
