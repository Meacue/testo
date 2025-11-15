<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\ObjectType;
use Testo\Assert\State\AssertException;
use Testo\Assert\State\AssertTypeFailure;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Assertion utilities for iterables.
 *
 * @internal
 */
class AssertObject implements ObjectType
{
    public function __construct(
        private readonly object $value,
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
        \is_object($value) or StaticState::fail(AssertTypeFailure::create('iterable', $value));

        StaticState::log('Assert iterable: ' . Support::stringify($value));
        return new self($value);
    }

    /**
     * Asserts that the object is an instance of the given class/interface.
     * @param string $class Fully-qualified class or interface name.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function instanceOf(string $class, string $message = ''): self
    {
        // @todo
        return new self($this->value);
    }

    /**
     * Asserts that the object has the given property.
     * @param string $propertyName The property name to check.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function hasProperty(string $propertyName, string $message = ''): self
    {
        // @todo
        return new self($this->value);
    }
}
