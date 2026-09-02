<?php

declare(strict_types=1);

namespace Tests\Test\Feature;

use Testo\Application\Application;
use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Context\RunResult;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Test\Internal\SkipInterceptor;
use Testo\Test\Skip;

/**
 * Session-level arithmetic for parked tests: they are counted, not lost — and they never
 * turn a run red on their own.
 */
#[Test]
#[Covers(Skip::class)]
#[Covers(SkipInterceptor::class)]
final class SkipSummaryTest
{
    /**
     * The classic off-by-parked bug: totals must satisfy `total = passed + failed + skipped`,
     * and the data-driven parked test counts exactly once.
     */
    public function totalsAddUpWithParkedTests(): void
    {
        $summary = self::run(__DIR__ . '/../Stub/SkipSummary/Mixed')->summary;

        Assert::same($summary->count(Status::Passed), 1);
        Assert::same($summary->count(Status::Failed), 1);
        Assert::same($summary->count(Status::Skipped), 2);
        Assert::same(
            $summary->total(),
            $summary->passed() + $summary->failed() + $summary->count(Status::Skipped),
        );
    }

    public function failingNeighborStillFailsTheRun(): void
    {
        $result = self::run(__DIR__ . '/../Stub/SkipSummary/Mixed');

        Assert::same($result->status, Status::Failed);
    }

    /**
     * A run consisting only of `#[Skip]`-marked tests is a success: Skipped is neither a
     * success nor a failure, so nothing fails the run.
     */
    public function runOfOnlyParkedTestsIsSuccessful(): void
    {
        $result = self::run(__DIR__ . '/../Stub/SkipSummary/OnlyParked');

        Assert::same($result->status, Status::Passed);
        Assert::same($result->summary->count(Status::Skipped), 2);
        Assert::same($result->summary->total(), 2);
    }

    private static function run(string $catalog): RunResult
    {
        return Application::createFromConfig(new ApplicationConfig(
            src: [],
            suites: [
                new SuiteConfig(
                    'SkipSummary',
                    location: new FinderConfig(include: [$catalog]),
                ),
            ],
        ))->run();
    }
}
