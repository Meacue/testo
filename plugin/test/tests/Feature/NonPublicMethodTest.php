<?php

declare(strict_types=1);

namespace Tests\Test\Feature;

use Testo\Assert;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Traits\TestRunner;
use Tests\Test\Stub\NonPublicTestMethods;

#[TestingSuite(path: __DIR__ . '/../Stub/NonPublicTestMethods.php')]
final class NonPublicMethodTest
{
    /**
     * Sanity check: a public method with #[Test] still runs.
     */
    #[Test]
    public function publicMethodIsExecuted(): void
    {
        $result = TestRunner::runTest([NonPublicTestMethods::class, 'publicTest']);

        Assert::same($result->status, Status::Passed);
    }

    /**
     * A protected method with #[Test] is discovered and executed successfully.
     */
    #[Test]
    public function protectedMethodIsExecuted(): void
    {
        $result = TestRunner::runTest([NonPublicTestMethods::class, 'protectedTest']);

        Assert::same($result->status, Status::Passed);
    }

    /**
     * A private method with #[Test] is discovered and executed successfully.
     */
    #[Test]
    public function privateMethodIsExecuted(): void
    {
        $result = TestRunner::runTest([NonPublicTestMethods::class, 'privateTest']);

        Assert::same($result->status, Status::Passed);
    }
}
