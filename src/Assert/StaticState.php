<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Assert\Exception\StateNotFound;
use Testo\Assert\Internal\Expectation\ExpectedFail;
use Testo\Assert\Internal\Expectation\ExpectExceptionHandler;
use Testo\Assert\Internal\Expectation\Leaks;
use Testo\Assert\Internal\Expectation\NotLeaks;
use Testo\Assert\State\AssertException;
use Testo\Assert\State\AssertionComposite;
use Testo\Assert\State\AssertionException;
use Testo\Assert\State\Success;

/**
 * Holds the current assertion collector.
 *
 * @internal
 * @psalm-internal Testo\Assert
 */
final class StaticState
{
    public static ?TestState $state = null;

    /**
     * Swap the current collector with the given one.
     *
     * @return TestState|null The previous collector.
     */
    public static function swap(?TestState $collector): ?TestState
    {
        [self::$state, $collector] = [$collector, self::$state];
        return $collector;
    }

    /**
     * Get the current collector.
     */
    public static function current(): ?TestState
    {
        return self::$state;
    }

    /**
     * Success type assertion.
     *
     * @param non-empty-string $type The expected type.
     */
    public static function typeSuccess(string $type, mixed $actual, string $message = ''): AssertionComposite
    {
        $result = new AssertionComposite(Support::stringify($actual), "is $type", $message);
        self::$state === null or self::$state->history[] = $result;
        return $result;
    }

    /**
     * Register a type assertion failure and throw an exception.
     *
     * @param non-empty-string $type The expected type.
     */
    public static function typeFail(string $type, mixed $actual, string $message = ''): never
    {
        $result = new AssertionException(
            value: Support::stringify($actual),
            assertion: "is $type",
            context: $message,
            reason: "got " . Support::stringify($actual),
            details: '',
        );
        self::$state === null or self::$state->history[] = $result;
        throw $result;
    }

    /**
     * @param non-empty-string $assertion The assertion result (e.g., "Same: 42", "Assert `true`").
     * @param string $context Optional user-provided context describing what is being asserted.
     */
    public static function log(string $assertion, string $context = ''): void
    {
        self::$state === null or self::$state->history[] = new Success(
            assertion: $assertion,
            context: $context,
        );
    }

    /**
     * Log a failed assertion and throw the given exception.
     */
    public static function fail(AssertException $failure): never
    {
        self::$state === null or self::$state->history[] = $failure;
        throw $failure;
    }

    /**
     * Set the expected exception for the current test.
     *
     * @param class-string|\Throwable $classOrObject The expected exception class, interface, or an exception object.
     *
     * @throws \RuntimeException when there is no current {@see TestState}.
     */
    public static function expectException(
        string|\Throwable $classOrObject,
    ): ExpectExceptionHandler {
        self::$state === null and throw new StateNotFound();
        return self::$state->expectations[] = new ExpectExceptionHandler($classOrObject);
    }

    public static function expectFail(AssertException $exception): void
    {
        self::$state === null and throw new StateNotFound();
        self::$state->expectations[] = new ExpectedFail($exception);
    }

    /**
     * Track the given objects in the current test state to detect memory leaks.
     *
     * @param bool $notLeaks Whether to expect no leaks (true) or expect leaks (false).
     * @param object ...$objects The objects to track.
     * @return ($notLeaks is true ? NotLeaks : Leaks) The created expectation.
     */
    public static function trackObjectsLeak(bool $notLeaks, object ...$objects): Leaks|NotLeaks
    {
        self::$state === null and throw new StateNotFound();
        return self::$state->expectations[] = $notLeaks
            ? new NotLeaks(...$objects)
            : new Leaks(...$objects);
    }
}
