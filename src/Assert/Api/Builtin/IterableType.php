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
     */
    public function contains(mixed $needle, string $message = ''): self;

    /**
     * Asserts that the iterable has the same number of elements as the expected iterable.
     *
     * @param iterable $expected The iterable to compare size against.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function sameSizeAs(iterable $expected, string $message = ''): self;

    /**
     * Asserts that the iterable has the expected number of elements.
     *
     * @param int $expected The expected count of elements.
     * @throws AssertException when the assertion fails.
     */
    public function hasCount(int $expected): self;

    /**
     * Asserts that all values in the iterable are of the specified type.
     *
     * @param non-empty-string $type The expected type name (e.g., 'int', 'string', 'object', class name)
     *        considered valid by {@see \get_debug_type()}.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function allOf(string $type, string $message = ''): self;
}
