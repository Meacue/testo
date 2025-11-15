<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\ArrayType;
use Testo\Assert\Internal\Assertion\Traits\IterableTrait;
use Testo\Assert\State\AssertException;
use Testo\Assert\State\AssertTypeFailure;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Assertion utilities for iterables.
 *
 * @internal
 */
class AssertArray implements ArrayType
{
    use IterableTrait;

    public function __construct(
        private readonly array $value,
    ) {}

    /**
     * Validate that the given value is a float and return an AssertFloat instance.
     *
     * @param mixed $value The value to be asserted as float.
     * @return self An instance of AssertFloat.
     * @throws AssertTypeFailure when the value is not a float.
     */
    public static function validateAndCreate(mixed $value): self
    {
        \is_array($value) or StaticState::fail(AssertTypeFailure::create('iterable', $value));

        StaticState::log('Assert iterable: ' . Support::stringify($value));
        return new self($value);
    }

    /**
     * Asserts that the array contains given key.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function hasKey(mixed $key, string $message = ''): self
    {
        // @todo
        return new self($this->value);
    }
}
