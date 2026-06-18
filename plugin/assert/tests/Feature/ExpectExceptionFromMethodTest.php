<?php

declare(strict_types=1);

namespace Tests\Assert\Feature;

use Testo\Assert;
use Testo\Assert\Api\ExpectedException;
use Testo\Assert\Internal\Expectation\ExpectExceptionHandler;
use Testo\Assert\State\Expectation;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Expect;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Assert\Stub\ExceptionThrower;
use Tests\Assert\Stub\ExpectExceptionFromMethod;

/**
 * Negative scenarios for {@see ExpectedException::fromMethod()} (origin detection via stack trace),
 * observed through the runner.
 *
 * @see Expect::exception()
 */
#[Test]
#[Covers(Expect::class, 'exception')]
#[Covers(ExpectExceptionHandler::class)]
#[TestingSuite(path: __DIR__ . '/../Stub')]
final class ExpectExceptionFromMethodTest
{
    /**
     * Exception originates from a class other than the expected one.
     */
    public function wrongClass(): void
    {
        $result = TestRunner::runTest([ExpectExceptionFromMethod::class, 'wrongClass']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Expectation::class);
        Assert::string($result->failure->getFailReason())
            ->contains('stdClass::wrongClass()');
    }

    /**
     * Expected origin method name does not exist in the trace.
     */
    public function wrongMethod(): void
    {
        $result = TestRunner::runTest([ExpectExceptionFromMethod::class, 'wrongMethod']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Expectation::class);
        Assert::string($result->failure->getFailReason())
            ->contains(ExceptionThrower::class . '::nonExistent()');
    }

    /**
     * One of several chained fromMethod() conditions does not match.
     */
    public function multipleOneWrong(): void
    {
        $result = TestRunner::runTest([ExpectExceptionFromMethod::class, 'multipleOneWrong']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Expectation::class);
        Assert::string($result->failure->getFailReason())
            ->contains(ExceptionThrower::class . '::nonExistent()');
    }

    /**
     * The method exists in the codebase but is absent from this exception's trace.
     */
    public function methodNotInTrace(): void
    {
        $result = TestRunner::runTest([ExpectExceptionFromMethod::class, 'methodNotInTrace']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Expectation::class);
        Assert::string($result->failure->getFailReason())
            ->contains(ExceptionThrower::class . '::deepThrow()');
    }
}
