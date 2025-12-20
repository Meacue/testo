<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Expectation;

use Testo\Assert\Api\ExpectedException;
use Testo\Assert\State\AssertException;
use Testo\Assert\State\Assertion;
use Testo\Assert\State\AssertionComposite;
use Testo\Assert\State\AssertionException;
use Testo\Assert\State\AssertionSuccess;
use Testo\Assert\State\Record;
use Testo\Assert\State\Success;
use Testo\Assert\Support;
use Testo\Assert\TestState;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestResult;

/**
 * Expected exception declaration.
 *
 * @internal
 */
final class ExpectExceptionHandler implements ExpectedException
{
    /**
     * @param class-string|\Throwable $classOrObject Expected exception class, interface, or an object.
     */
    public function __construct(
        public readonly string|\Throwable $classOrObject,
    ) {}

    /**
     * The expected exception was thrown by the given method.
     *
     * Does not mean that tne exception was created in that method, only that the method was the point
     * where the exception was thrown.
     *
     * @param class-string $class Fully qualified class name.
     * @param string $method Method name.
     */
    public function fromMethod(string $class, string $method): self
    {
        throw new \LogicException('Not implemented yet');
    }

    /**
     * The expected exception should have the exact message.
     */
    public function withMessage(string $message): self
    {
        throw new \LogicException('Not implemented yet');
    }

    /**
     * The expected exception message should match the given pattern.
     *
     * @param non-empty-string $pattern Regex pattern.
     */
    public function withMessagePattern(string $pattern): self
    {
        throw new \LogicException('Not implemented yet');
    }

    /**
     * The expected exception message should contain the given substring.
     *
     * @param non-empty-string $substring Substring to search for.
     */
    public function withMessageContaining(string $substring): self
    {
        throw new \LogicException('Not implemented yet');
    }

    /**
     * The expected exception should have the given code or one of the given codes.
     *
     * @param int|list<int> $code Expected code or list of expected codes.
     */
    public function withCode(int|array $code): self
    {
        throw new \LogicException('Not implemented yet');
    }

    /**
     * The expected exception should not have a previous exception.
     */
    public function withoutPrevious(): self
    {
        throw new \LogicException('Not implemented yet');
    }

    /**
     * The expected exception was caused by the given previous exception.
     *
     * @param class-string|\Throwable $classOrObject Expected previous exception class, interface, or an object.
     * @param (callable(self): mixed)|null $assertion Optional assertion callback for the previous exception.
     */
    public function withPrevious(\Throwable|string $classOrObject, ?callable $assertion = null): self
    {
        throw new \LogicException('Not implemented yet');
    }

    public function __invoke(TestResult $result, TestState $state): TestResult
    {
        # An expectation was defined
        # Check if the expectation was met
        $record = $this->isPassed($result->failure);
        $state->history[] = $record;

        return $record->isSuccess()
            ? $result->with(status: Status::Passed)
            : $result->with(status: Status::Failed)->withFailure($record);
    }

    private function isPassed(?\Throwable $actual): Assertion|\Throwable
    {
        $class = \is_string($this->classOrObject) ? $this->classOrObject : $this->classOrObject::class;
        if (\is_object($this->classOrObject) ? ($actual === $this->classOrObject) : ($actual instanceof $class)) {
            return new AssertionComposite(
                value: Support::stringify($actual),
                assertion: $class === $actual::class
                    ? 'is thrown'
                    : 'is thrown as an instance of ' . $class,
                context: '',
            );
        }

        return new AssertionException(
            value: Support::stringify($actual),
            assertion: 'is thrown as an instance of ' . $class,
            context: '',
            reason: $actual === null ? 'none thrown' : 'got ' . $actual::class,
            details: '',
        );
    }
}
