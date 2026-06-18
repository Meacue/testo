<?php

declare(strict_types=1);

namespace Tests\Test\Feature;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Test\Internal\TestoAttributesLocatorInterceptor;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Test\Stub\NonPublicTestMethods;

#[Test]
#[Covers(TestoAttributesLocatorInterceptor::class)]
#[TestingSuite(path: __DIR__ . '/../Stub/NonPublicTestMethods.php')]
final class NonPublicMethodTest
{
    /**
     * Sanity check: a public method with #[Test] still runs.
     */
    public function publicMethodIsExecuted(): void
    {
        $result = TestRunner::runTest([NonPublicTestMethods::class, 'publicTest']);

        Assert::same($result->status, Status::Passed);
    }

    /**
     * A protected method with #[Test] is discovered and executed successfully.
     */
    public function protectedMethodIsExecuted(): void
    {
        $result = TestRunner::runTest([NonPublicTestMethods::class, 'protectedTest']);

        Assert::same($result->status, Status::Passed);
    }

    /**
     * A private method with #[Test] is discovered and executed successfully.
     */
    public function privateMethodIsExecuted(): void
    {
        $result = TestRunner::runTest([NonPublicTestMethods::class, 'privateTest']);

        Assert::same($result->status, Status::Passed);
    }
}
