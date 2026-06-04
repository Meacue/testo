<?php

declare(strict_types=1);

namespace Tests\Repeat\Feature;

use Testo\Assert;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Tests\Repeat\Stub\RepeatClassLevelStub;
use Tests\Repeat\Stub\RepeatFailingStub;
use Tests\Repeat\Stub\RepeatFlakyStub;
use Tests\Repeat\Stub\RepeatPassingStub;

#[Test]
#[TestingSuite(path: __DIR__ . '/../Stub')]
final class RepeatFeatureTest
{
    public function defaultRepeatPasses(): void
    {
        $result = TestRunner::runTest([RepeatPassingStub::class, 'defaultRepeat']);

        Assert::same($result->status, Status::Passed);
    }

    public function repeatThreeTimesPasses(): void
    {
        $result = TestRunner::runTest([RepeatPassingStub::class, 'repeatThreeTimes']);

        Assert::same($result->status, Status::Passed);
    }

    public function repeatOncePasses(): void
    {
        $result = TestRunner::runTest([RepeatPassingStub::class, 'repeatOnce']);

        Assert::same($result->status, Status::Passed);
    }

    public function failsOnSecondIteration(): void
    {
        $result = TestRunner::runTest([RepeatFailingStub::class, 'failsOnSecondIteration']);

        Assert::same($result->status, Status::Failed);
    }

    public function failsImmediately(): void
    {
        $result = TestRunner::runTest([RepeatFailingStub::class, 'failsImmediately']);

        Assert::same($result->status, Status::Failed);
    }

    public function classLevelRepeatFirstTest(): void
    {
        $result = TestRunner::runTest([RepeatClassLevelStub::class, 'firstTest']);

        Assert::same($result->status, Status::Passed);
    }

    public function classLevelRepeatSecondTest(): void
    {
        $result = TestRunner::runTest([RepeatClassLevelStub::class, 'secondTest']);

        Assert::same($result->status, Status::Passed);
    }

    public function failureWithinThresholdMarksFlaky(): void
    {
        $result = TestRunner::runTest([RepeatFlakyStub::class, 'failureWithinThreshold']);

        Assert::same($result->status, Status::Flaky);
    }

    public function failureExceedsThresholdFails(): void
    {
        $result = TestRunner::runTest([RepeatFlakyStub::class, 'failureExceedsThreshold']);

        Assert::same($result->status, Status::Failed);
    }

    public function failureWithinThresholdStaysPassedWhenMarkFlakyIsFalse(): void
    {
        $result = TestRunner::runTest([RepeatFlakyStub::class, 'failureWithinThresholdNoFlakyMark']);

        Assert::same($result->status, Status::Passed);
    }
}
