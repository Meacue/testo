<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\StringType;
use Testo\Assert\State\AssertException;
use Testo\Assert\State\AssertTypeFailure;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Assertion utilities for string data type.
 *
 * @internal
 */
class AssertString implements StringType
{
    private function __construct(
        private readonly string $value,
    ) {}

    /**
     * Validate that the given value is a string and return an AssertString instance.
     *
     * @param mixed $value The value to be asserted as string.
     * @return self An instance of AssertString.
     *
     * @throws AssertTypeFailure when the value is not a string.
     */
    public static function create(mixed $value): self
    {
        \is_string($value) or StaticState::fail(AssertTypeFailure::create('string', $value));

        StaticState::log('Assert string: ' . Support::stringify($value));
        return new self($value);
    }

    /**
     * Asserts that the string contains the given substring.
     *
     * @param string $needle Substring to search for.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function contains(string $needle, string $message = ''): static
    {
        if (\str_contains($this->value, $needle)) {
            StaticState::log('String contains "' . $needle . '"', $message);
            return $this;
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
