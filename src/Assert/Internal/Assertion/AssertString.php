<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\StringType;
use Testo\Assert\Internal\StaticState;
use Testo\Assert\State\AssertException;
use Testo\Assert\State\Assertion\AssertionComposite;
use Testo\Assert\State\Assertion\AssertionException;

/**
 * Assertion utilities for string data type.
 *
 * @internal
 */
class AssertString implements StringType
{
    public function __construct(
        private readonly string $value,
        private readonly AssertionComposite $parent,
    ) {}

    /**
     * Validate that the given value is a string and return an AssertString instance.
     *
     * @param mixed $value The value to be asserted as string.
     * @return self An instance of AssertString.
     *
     * @throws AssertionException when the value is not a string.
     */
    public static function validateAndCreate(mixed $value): self
    {
        \is_string($value) or StaticState::typeFail('string', $value);

        $parent = StaticState::typeSuccess('string', $value);
        return new self($value, $parent);
    }

    /**
     * Asserts that the string contains the given substring.
     *
     * @param string $needle Substring to search for.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    #[\Override]
    public function contains(string $needle, string $message = ''): static
    {
        if (\str_contains($this->value, $needle)) {
            StaticState::success($this->value, ' contains "' . $needle . '"', $message);
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

    /**
     * Asserts that the string does not contain the given substring.
     *
     * @param string $needle Substring to search for.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    #[\Override]
    public function notContains(string $needle, string $message = ''): static
    {
        if (!\str_contains($this->value, $needle)) {
            StaticState::success($this->value, 'does not contain "' . $needle . '"', $message);
            return $this;
        }

        StaticState::fail(AssertException::compare(
            $needle,
            $this->value,
            $message,
            pattern: 'Failed asserting that string `%2$s` does not contain `%1$s`.',
            showDiff: false,
        ));
    }
}
