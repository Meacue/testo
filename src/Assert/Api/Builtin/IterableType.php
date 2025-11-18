<?php

declare(strict_types=1);

namespace Testo\Assert\Api\Builtin;

use Testo\Assert\State\AssertException;

/**
 * Assertion utilities for iterables.
 *
 * The {@see iterable} type includes {@see array} and objects implementing {@see \Traversable} interface.
 *
 * @note A {@see \Generator} can be iterated only once. Using this interface on a generator will exhaust it.
 */
interface IterableType
{
    /**
     * Asserts that the iterable contains the given needle.
     *
     * @param mixed $needle The value to look for within the iterable.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     *
     * @deprecated To be implemented
     */
    public function contains(mixed $needle, string $message = ''): self;

    /**
     * Asserts that the iterable has the same number of elements as the expected iterable.
     *
     * @param iterable $expected The iterable to compare size against.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     *
     * @deprecated To be implemented
     */
    public function sameSizeAs(iterable $expected, string $message = ''): self;
}
