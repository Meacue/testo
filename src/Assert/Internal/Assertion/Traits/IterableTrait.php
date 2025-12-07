<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion\Traits;

use Testo\Assert\State\AssertException;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Contains assertion methods for iterable values.
 *
 * @property iterable $value
 */
trait IterableTrait
{
    #[\Override]
    public function contains(mixed $needle, string $message = ''): self
    {
        foreach ($this->value as $item) {
            if ($item === $needle) {
                StaticState::log('Assert contains: ' . Support::stringify($needle) . '.');
                return new self($this->value);
            }
        }

        StaticState::fail(
            AssertException::fail(
                \sprintf(
                    'Failed to assert that %s contains %s.',
                    Support::stringify($this->value),
                    Support::stringify($needle),
                ),
            ),
        );
    }

    #[\Override]
    public function sameSizeAs(iterable $expected, string $message = ''): self
    {
        if (self::countIterable($this->value) === self::countIterable($expected)) {
            StaticState::log('Assert same size as: ' . Support::stringify($expected) . '.');
            return new self($this->value);
        }

        StaticState::fail(
            AssertException::fail(
                \sprintf(
                    'Failed to assert that iterable %s has the same number of elements as %s.',
                    Support::stringify($this->value),
                    Support::stringify($expected),
                ),
            ),
        );
    }

    #[\Override]
    public function allOf(string $type, string $message = ''): self
    {
        $type = \strtolower($type);
        $type = match ($type) {
            'integer' => 'int',
            'double' => 'float',
            'boolean' => 'bool',
            default => $type,
        };
        foreach ($this->value as $element) {
            $actualType = \strtolower(\get_debug_type($element));
            $actualType === $type or StaticState::fail(
                AssertException::fail(
                    \sprintf(
                        'Failed to assert that all elements of iterable %s have type %s (found %s instead).',
                        Support::stringify($this->value),
                        Support::stringify($type),
                        Support::stringify($actualType),
                    ),
                ),
            );
        }

        StaticState::log(
            \sprintf(
                'Assert all elements are of type %s.',
                Support::stringify($type),
            ),
        );
        return new self($this->value);
    }

    #[\Override]
    public function hasCount(int $expected): self
    {
        $count = self::countIterable($this->value);
        if ($count === $expected) {
            StaticState::log("Assert count: {$count}.");
            return new self($this->value);
        }

        StaticState::fail(
            AssertException::fail(
                \sprintf(
                    'Failed to assert that %s has %d elements (found %d instead).',
                    Support::stringify($this->value),
                    $expected,
                    $count,
                ),
            ),
        );
    }

    /**
     * Counts the number of elements in the given iterable.
     */
    private static function countIterable(iterable $value): int
    {
        // if Countable
        if (\is_array($value) || $value instanceof \Countable) {
            return \count($value);
        }

        // if Traversable
        $count = 0;
        foreach ($value as $_) {
            $count++;
        }

        return $count;
    }
}
