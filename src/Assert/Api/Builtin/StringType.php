<?php

declare(strict_types=1);

namespace Testo\Assert\Api\Builtin;

use Testo\Assert\State\AssertException;

/**
 * Assertion utilities for string data type.
 */
interface StringType
{
    /**
     * Asserts that the string contains the given substring.
     *
     * @param string $needle Substring to search for.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function contains(string $needle, string $message = ''): static;

    /**
     * Asserts that the string does not contain the given substring.
     *
     * @param string $needle Substring to search for.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function notContains(string $needle, string $message = ''): static;
}
