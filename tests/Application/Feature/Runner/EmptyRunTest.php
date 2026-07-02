<?php

declare(strict_types=1);

namespace Tests\Application\Feature\Runner;

use Testo\Application\Application;
use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Test;

/**
 * A run that discovers no tests — empty suite, or every test filtered out — verified nothing and
 * must not be reported as a success: {@see Application::run()} flags it {@see Status::Risky}, which
 * makes {@see Status::isSuccessful()} false and the process exit code non-zero.
 */
#[Test]
#[Covers(Application::class)]
final class EmptyRunTest
{
    public function emptyRunIsRiskyNotPassed(): void
    {
        $result = self::run(__DIR__ . '/../../Stub/EmptyRun');

        Assert::same($result->status, Status::Risky);
        Assert::same($result->summary->total(), 0);
        Assert::false($result->status->isSuccessful());
    }

    private static function run(string $path): \Testo\Core\Context\RunResult
    {
        $app = Application::createFromConfig(new ApplicationConfig(
            src: [],
            suites: [
                new SuiteConfig('Empty', location: new FinderConfig(include: [$path])),
            ],
        ));

        return $app->run();
    }
}
