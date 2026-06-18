<?php

declare(strict_types=1);

namespace Tests\Retry\Feature;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Retry;
use Testo\Retry\Interceptor\RetryPolicyRunInterceptor;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Retry\Stub\RetryClassLevelStub;
use Tests\Retry\Stub\RetryFailingStub;
use Tests\Retry\Stub\RetryFlakyStub;
use Tests\Retry\Stub\RetryNoFlakyMarkStub;
use Tests\Retry\Stub\RetryPassingStub;

#[Test]
#[TestingSuite(path: __DIR__ . '/../Stub')]
#[Covers(Retry::class)]
#[Covers(RetryPolicyRunInterceptor::class)]
final class RetryFeatureTest
{
    public function passesFirstTimeReturnsPassed(): void
    {
        $result = TestRunner::runTest([RetryPassingStub::class, 'passesFirstTime']);

        Assert::same($result->status, Status::Passed);
    }

    public function passesOnSecondAttemptReturnsFlaky(): void
    {
        $result = TestRunner::runTest([RetryFlakyStub::class, 'passesOnSecondAttempt']);

        Assert::same($result->status, Status::Flaky);
    }

    public function passesOnThirdAttemptReturnsFlaky(): void
    {
        $result = TestRunner::runTest([RetryFlakyStub::class, 'passesOnThirdAttempt']);

        Assert::same($result->status, Status::Flaky);
    }

    public function alwaysFailsReturnsFailed(): void
    {
        $result = TestRunner::runTest([RetryFailingStub::class, 'alwaysFails']);

        Assert::same($result->status, Status::Failed);
    }

    public function singleAttemptDoesNotRetry(): void
    {
        $result = TestRunner::runTest([RetryFailingStub::class, 'singleAttemptNoRetry']);

        Assert::same($result->status, Status::Failed);
    }

    public function markFlakyFalseKeepsPassedStatus(): void
    {
        $result = TestRunner::runTest([RetryNoFlakyMarkStub::class, 'passesOnSecondAttempt']);

        Assert::same($result->status, Status::Passed);
    }

    public function classLevelRetryAppliesToTests(): void
    {
        $result = TestRunner::runTest([RetryClassLevelStub::class, 'passesOnThirdAttempt']);

        Assert::same($result->status, Status::Flaky);
    }
}
