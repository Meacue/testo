<?php

declare(strict_types=1);

namespace Testo\Assert\Api\Builtin;

use Testo\Assert\State\AssertException;

/**
 * Assertion utilities for objects.
 */
interface ObjectType
{
    /**
     * Asserts that the object is an instance of the given class/interface.
     * @param string $class Fully-qualified class or interface name.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function instanceOf(string $class, string $message = ''): self;

    /**
     * Asserts that the object has the given property.
     * @param string $propertyName The property name to check.
     * @param string $message Optional message for the assertion.
     * @throws AssertException when the assertion fails.
     */
    public function hasProperty(string $propertyName, string $message = ''): self;
}
