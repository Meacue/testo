<?php

declare(strict_types=1);

namespace Testo\Assert\Api;

/**
 * Expected exception API.
 */
interface ExpectedException
{
    /**
     * The expected exception was thrown by the given method.
     *
     * Does not mean that tne exception was created in that method, only that the method was the point
     * where the exception was thrown.
     *
     * @param class-string $class Fully qualified class name.
     * @param string $method Method name.
     *
     * @deprecated To be implemented
     */
    public function fromMethod(string $class, string $method): self;

    /**
     * The expected exception should have the exact message.
     *
     * @deprecated To be implemented
     */
    public function withMessage(string $message): self;

    /**
     * The expected exception message should match the given pattern.
     *
     * @param non-empty-string $pattern Regex pattern.
     *
     * @deprecated To be implemented
     */
    public function withMessagePattern(string $pattern): self;

    /**
     * The expected exception message should contain the given substring.
     *
     * @param non-empty-string $substring Substring to search for.
     *
     * @deprecated To be implemented
     */
    public function withMessageContaining(string $substring): self;

    /**
     * The expected exception should have the given code or one of the given codes.
     *
     * @param int|list<int> $code Expected code or list of expected codes.
     *
     * @deprecated To be implemented
     */
    public function withCode(int|array $code): self;

    /**
     * The expected exception should not have a previous exception.
     *
     * @deprecated To be implemented
     */
    public function withNoPrevious(): self;

    /**
     * The expected exception was caused by the given previous exception.
     *
     * @param class-string|\Throwable $classOrObject Expected previous exception class, interface, or an object.
     * @param (callable(self): mixed)|null $assertion Optional assertion callback for the previous exception.
     *
     * @deprecated To be implemented
     */
    public function withPrevious(\Throwable|string $classOrObject, ?callable $assertion = null): self;
}
