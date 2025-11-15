<?php

declare(strict_types=1);

namespace Testo\Assert\Api\Builtin;

use Testo\Assert\State\AssertException;

/**
 * Assertion utilities for array-like data type.
 */
interface ArrayType extends IterableType
{
    /**
     * Asserts that the array contains given key.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function hasKey(mixed $key, string $message = ''): self;
}
