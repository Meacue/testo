<?php

declare(strict_types=1);

namespace Testo\Assert\DataType\Builtin;

use Testo\Assert\State\AssertException;
use Testo\Assert\StaticState;

/**
 * Assertion utilities for string data type.
 */
class AssertString
{
    public function __construct(
        public string $value,
    ) {}

    /**
     * Asserts that the string contains the given substring.
     *
     * @param string $needle Substring to search for.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function contains(string $needle, string $message = ''): void
    {
        if (\str_contains($this->value, $needle)) {
            StaticState::log('String contains "' . $needle . '"', $message);
            return;
        }

        StaticState::fail(AssertException::compare(
            $needle,
            $this->value,
            $message,
            pattern: 'Failed asserting that string `%2$s` contains `%1$s`.',
            showDiff: false,
        ));
    }
}
