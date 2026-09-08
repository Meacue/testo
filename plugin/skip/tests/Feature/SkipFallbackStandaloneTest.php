<?php

declare(strict_types=1);

namespace Tests\Skip\Feature;

use Testo\Application\Application;
use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\Plugin\SuitePlugins;
use Testo\Application\Config\SuiteConfig;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Convention\NamingConventionPlugin;
use Testo\Core\Context\TestResult;
use Testo\Core\Exception\SkipTest;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Skip\Internal\SkipInterceptor;
use Testo\Skip;
use Testo\Test\TestPlugin;
use Tests\Skip\Stub\SkipStandalone\StandaloneSkippedTest;

/**
 * The standalone contract of `#[Skip]`: with `TestPlugin` not registered, the attribute's
 * {@see \Testo\Pipeline\Attribute\FallbackInterceptor} declaration alone skips a class-level
 * case (tests are discovered by naming convention, so no `#[Test]` attribute is involved).
 */
#[Test]
#[Covers(Skip::class)]
#[Covers(SkipInterceptor::class)]
final class SkipFallbackStandaloneTest
{
    public function classLevelSkipFallsBackWithoutTestPlugin(): void
    {
        $run = Application::createFromConfig(new ApplicationConfig(
            src: [],
            suites: [
                new SuiteConfig(
                    'SkipStandalone',
                    location: new FinderConfig(include: [__DIR__ . '/../Stub/SkipStandalone']),
                    plugins: SuitePlugins::without(TestPlugin::class)->with(new NamingConventionPlugin()),
                ),
            ],
        ))->run();

        /** @var list<TestResult> $tests */
        $tests = [];
        foreach ($run as $suite) {
            foreach ($suite as $case) {
                foreach ($case as $test) {
                    $tests[] = $test;
                }
            }
        }

        # No TestPlugin in this run: the interceptor the attribute spawns through its own
        # #[FallbackInterceptor] is what reports both tests of the case.
        Assert::count($tests, 2);

        $messages = [];
        foreach ($tests as $test) {
            Assert::same($test->status, Status::Skipped);
            Assert::instanceOf($test->failure, SkipTest::class);
            $messages[] = $test->failure?->getMessage();
        }

        # The order the results are appended in is not a contract; the composed messages are —
        # the `is skipped via #[Skip]` marker and the class-level reason.
        \sort($messages);
        Assert::same($messages, [
            StandaloneSkippedTest::class . '::testFirstSkipped is skipped via #[Skip] ==> standalone case is skipped',
            StandaloneSkippedTest::class . '::testSecondSkipped is skipped via #[Skip] ==> standalone case is skipped',
        ]);
    }
}
