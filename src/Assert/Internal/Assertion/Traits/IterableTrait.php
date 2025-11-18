<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion\Traits;

use Testo\Assert\State\AssertException;

/**
 * Contains methods for comparing numeric values
 */
trait IterableTrait
{
    /**
     * Asserts that the iterable contains the given needle.
     * @param mixed $needle The value to look for within the iterable.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function contains(mixed $needle, string $message = ''): self
    {
        throw new \LogicException('Not implemented yet');
    }

    /**
     * Asserts that the iterable has the same number of elements as the expected iterable.
     * @param iterable $expected The iterable to compare size against.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function sameSizeAs(iterable $expected, string $message = ''): self
    {
        throw new \LogicException('Not implemented yet');
    }

    public function allOf(string $type, string $message = ''): \Testo\Assert\Api\Builtin\IterableType
    {
        throw new \LogicException('Not implemented yet');
    }
}
