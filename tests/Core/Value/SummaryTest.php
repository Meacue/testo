<?php

declare(strict_types=1);

namespace Tests\Core\Value;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Core\Value\Summary;
use Testo\Test;

#[Test]
#[Covers(Summary::class)]
final class SummaryTest
{
    public function emptyByDefault(): void
    {
        $summary = new Summary();

        Assert::same($summary->total(), 0);
        Assert::same($summary->count(Status::Passed), 0);
        Assert::same($summary->failed(), 0);
        Assert::same($summary->passed(), 0);
        Assert::same($summary->metric('assertions'), 0);
        Assert::same($summary->duration, 0.0);
    }

    public function forTestCountsASingleStatus(): void
    {
        $summary = Summary::forTest(Status::Passed, 1.5);

        Assert::same($summary->total(), 1);
        Assert::same($summary->count(Status::Passed), 1);
        Assert::same($summary->count(Status::Failed), 0);
        Assert::same($summary->duration, 1.5);
        Assert::same($summary->metrics, []);
    }

    public function failedCountsFailuresAndErrors(): void
    {
        $summary = Summary::combine([
            Summary::forTest(Status::Failed),
            Summary::forTest(Status::Error),
            Summary::forTest(Status::Passed),
        ]);

        Assert::same($summary->failed(), 2);
        Assert::same($summary->total(), 3);
    }

    public function passedCountsPassedAndFlaky(): void
    {
        $summary = Summary::combine([
            Summary::forTest(Status::Passed),
            Summary::forTest(Status::Flaky),
            Summary::forTest(Status::Failed),
        ]);

        Assert::same($summary->passed(), 2);
    }

    public function mergeSumsCountsMetricsAndDuration(): void
    {
        $a = Summary::forTest(Status::Passed, 1.0)->withAddedMetric('assertions', 3);
        $b = Summary::forTest(Status::Passed, 0.5)->withAddedMetric('assertions', 2);

        $merged = $a->merge($b);

        Assert::same($merged->count(Status::Passed), 2);
        Assert::same($merged->metric('assertions'), 5);
        Assert::same($merged->duration, 1.5);
    }

    public function mergeWithEmptyIsIdentity(): void
    {
        $base = Summary::forTest(Status::Failed, 2.0)->withAddedMetric('assertions', 4);

        $merged = $base->merge(new Summary());

        Assert::same($merged->count(Status::Failed), 1);
        Assert::same($merged->metric('assertions'), 4);
        Assert::same($merged->duration, 2.0);
    }

    public function combineFoldsEveryStatusAndDuration(): void
    {
        $summary = Summary::combine([
            Summary::forTest(Status::Passed, 1.0),
            Summary::forTest(Status::Passed, 2.0),
            Summary::forTest(Status::Failed, 4.0),
        ]);

        Assert::same($summary->total(), 3);
        Assert::same($summary->count(Status::Passed), 2);
        Assert::same($summary->count(Status::Failed), 1);
        Assert::same($summary->duration, 7.0);
    }

    public function combineOfNothingIsEmpty(): void
    {
        $summary = Summary::combine([]);

        Assert::same($summary->total(), 0);
        Assert::same($summary->duration, 0.0);
    }

    public function withAddedMetricCreatesThenIncrements(): void
    {
        $summary = (new Summary())
            ->withAddedMetric('assertions', 2)
            ->withAddedMetric('assertions', 3);

        Assert::same($summary->metric('assertions'), 5);
    }

    public function withAddedMetricIsImmutableAndKeepsCountsAndDuration(): void
    {
        $base = Summary::forTest(Status::Passed, 2.0);

        $next = $base->withAddedMetric('assertions', 1);

        Assert::same($next->count(Status::Passed), 1);
        Assert::same($next->duration, 2.0);
        Assert::same($next->metric('assertions'), 1);
        Assert::notSame($base, $next);
        Assert::same($base->metric('assertions'), 0);
    }

    public function withStatusReplacesCountsButKeepsMetricsAndDuration(): void
    {
        $base = Summary::forTest(Status::Error, 1.0)->withAddedMetric('assertions', 7);

        $next = $base->withStatus(Status::Passed);

        Assert::same($next->count(Status::Passed), 1);
        Assert::same($next->count(Status::Error), 0);
        Assert::same($next->total(), 1);
        Assert::same($next->metric('assertions'), 7);
        Assert::same($next->duration, 1.0);
        Assert::notSame($base, $next);
        Assert::same($base->count(Status::Error), 1);
    }
}
