<?php

declare(strict_types=1);

namespace Tests\Assert\Feature;

use Testo\Assert;
use Testo\Assert\ExpectNoAssertions;
use Testo\Assert\Internal\Middleware\ExpectationsInterceptor;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Assert\Stub\Common;

/**
 * How a test that finishes successfully without making any assertion is reported.
 */
#[Test]
#[Covers(ExpectationsInterceptor::class)]
#[Covers(ExpectNoAssertions::class)]
#[TestingSuite(path: __DIR__ . '/../Stub')]
final class CommonTest
{
    public function noAssertions(): void
    {
        $result = TestRunner::runTest([Common::class, 'risky']);
        Assert::same($result->status, Status::Risky);
    }

    public function doesNotPerformAssertionsKeepsPassed(): void
    {
        $result = TestRunner::runTest([Common::class, 'noAssertionsAllowed']);
        Assert::same($result->status, Status::Passed);
    }

    public function assertingDespiteAttributeIsRisky(): void
    {
        $result = TestRunner::runTest([Common::class, 'assertsDespiteAttribute']);
        Assert::same($result->status, Status::Risky);
    }

    public function expectingExceptionDespiteAttributeIsRisky(): void
    {
        $result = TestRunner::runTest([Common::class, 'expectsExceptionDespiteAttribute']);
        Assert::same($result->status, Status::Risky);
    }
}
