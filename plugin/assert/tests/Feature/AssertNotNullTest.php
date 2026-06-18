<?php

declare(strict_types=1);

namespace Tests\Assert\Feature;

use Testo\Assert;
use Testo\Assert\State\Assertion;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Assert\Stub\AssertNotNullNegative;
use Tests\Assert\Stub\AssertNotNullPositive;

/**
 * Pass/fail behaviour and failure rendering of {@see Assert::notNull()}, observed through the runner.
 *
 * @see Assert::notNull()
 */
#[Test]
#[Covers(Assert::class, 'notNull')]
#[TestingSuite(path: __DIR__ . '/../Stub')]
final class AssertNotNullTest
{
    /**
     * Falsy-but-not-null values (0, '', false, [], 0.0) must pass notNull().
     */
    public function nonNullValuePasses(): void
    {
        $result = TestRunner::runTest([AssertNotNullPositive::class, 'falsyNonNullValues']);

        Assert::same($result->status, Status::Passed);
    }

    public function nullFails(): void
    {
        $result = TestRunner::runTest([AssertNotNullNegative::class, 'nullFails']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('expected a non-null value, got `null`');
    }

    public function nullFailsWithMessage(): void
    {
        $result = TestRunner::runTest([AssertNotNullNegative::class, 'nullFailsWithMessage']);

        Assert::same($result->status, Status::Failed);
        Assert::instanceOf($result->failure, Assertion::class);
        Assert::string($result->failure->getFailReason())
            ->contains('expected a non-null value, got `null`');
        Assert::same($result->failure->getContext(), 'Value must not be null.');
    }
}
