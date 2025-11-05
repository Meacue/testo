<?php

declare(strict_types=1);

namespace Testo;

use Testo\Assert\Expectation\ExpectedException;
use Testo\Assert\StaticState;

/**
 * Assertion utilities.
 */
final class Expect
{
    /**
     * Expects that the test will throw an exception of the given class.
     *
     * @param class-string|\Throwable $classOrObject The expected exception class, interface, or an exception object.
     *
     * @note Requires {@see ExpectationsInterceptor} to be registered.
     */
    public static function exception(
        string|\Throwable $classOrObject,
    ): ExpectedException {
        return StaticState::expectException($classOrObject);
    }

    /**
     * Asserts that the given objects do not leak memory after the test execution.
     *
     * @param string $message Optional message to associate with the leak expectation.
     * @param object ...$objects The objects to monitor for memory leaks.
     */
    public static function notLeaks(string $message = '', object ...$objects): void
    {
        StaticState::expectNotLeaks($message, ...$objects);
    }
}
