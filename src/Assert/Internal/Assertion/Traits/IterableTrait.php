<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion\Traits;

use Testo\Assert\State\AssertException;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Contains assertion methods for iterable values.
 * @property iterable $value
 */
trait IterableTrait
{
    /**
     * Asserts that the iterable contains the given needle (strict comparison).
     * @param mixed $needle The value to look for within the iterable.
     * @param string $message Optional message for the assertion.
     *
     * @throws AssertException when the assertion fails.
     */
    public function contains(mixed $needle, string $message = ''): self
    {
        foreach ($this->value as $item) {
            if ($item === $needle) {
                StaticState::log(
                    'Assert that iterable: ' . Support::stringify($this->value) . ' contains ' . Support::stringify($needle),
                );
                return new self($this->value);
            }
        }
        StaticState::fail(
            AssertException::fail(
                'Failed to assert that iterable ' . Support::stringify($this->value) . ' contains ' . Support::stringify($needle),
            ),
        );
    }

    /**
     * Asserts that the iterable has the same number of elements as the expected iterable.
     *
     * @param iterable $expected The iterable to compare size against.
     * @param string $message Optional message for the assertion.
     *
     * @throws AssertException When the iterables do not have the same size.
     */
    public function sameSizeAs(iterable $expected, string $message = ''): self
    {
        if (Support::countIterable($this->value) === Support::countIterable($expected)) {
            StaticState::log(
                'Assert that iterable: ' . Support::stringify($this->value) . ' has the same number of elements as ' . Support::stringify($expected),
            );
            return new self($this->value);
        }
        StaticState::fail(
            AssertException::fail(
                'Failed to assert that iterable ' . Support::stringify($this->value) . ' has the same number of elements as ' . Support::stringify($expected),
            ),
        );
    }

    /**
     * Asserts that all elements of the iterable have the given PHP type.
     *
     * The $type parameter uses PHP internal type names (compatible with gettype()),
     * e.g.: "integer", "double", "boolean", "string", "array", "object", "resource", "null".
     *
     * @param non-empty-string $type Expected PHP type name for all elements.
     * @param string $message Optional message for the assertion.
     *
     * @throws AssertException When at least one element has a different type.
     */
    public function allOf(string $type, string $message = ''): self
    {
        foreach ($this->value as $element) {
            $actualType = \gettype($element);
            if ($actualType !== $type) {
                StaticState::fail(
                    AssertException::fail(
                        'Failed to assert that all elements of iterable ' . Support::stringify($this->value) . ' have type ' . Support::stringify($type) .
                        ' (found ' . Support::stringify($actualType) . ' instead)',
                    ),
                );
            }
        }
        StaticState::log(
            'Assert that all elements of iterable ' . Support::stringify($this->value) . ' have type ' . Support::stringify($type),
        );
        return new self($this->value);
    }
}
