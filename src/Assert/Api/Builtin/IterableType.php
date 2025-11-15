<?php

declare(strict_types=1);

namespace Testo\Assert\Api\Builtin;

use Testo\Assert\State\AssertException;

/**
 * Assertion utilities for iterables.
 *
 * Iterables include arrays and objects implementing iterable interface.
 * Does not work with Generators.
 */
interface IterableType
{
    /**
     * Asserts that the iterable contains the given needle.
     * @param mixed $needle The value to look for within the iterable.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function contains(mixed $needle, string $message = ''): self;

    /**
     * Asserts that the iterable has the same number of elements as the expected iterable.
     * @param iterable $expected The iterable to compare size against.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function sameSizeAs(iterable $expected, string $message = ''): self;
}
