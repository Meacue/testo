<?php

declare(strict_types=1);

namespace Tests\Assert\Feature;

use Testo\Assert;
use Testo\Assert\Internal\Middleware\ExpectationsInterceptor;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Assert\Stub\Common;

/**
 * A test that finishes successfully without making any assertion is reported as Risky.
 */
#[Test]
#[Covers(ExpectationsInterceptor::class)]
#[TestingSuite(path: __DIR__ . '/../Stub')]
final class CommonTest
{
    public function noAssertions(): void
    {
        $result = TestRunner::runTest([Common::class, 'risky']);
        Assert::same($result->status, Status::Risky);
    }
}
