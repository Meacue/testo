<?php

declare(strict_types=1);

namespace Testo\Output\JUnit;

use Internal\Container\Container;
use Internal\Path;
use Testo\Common\EventListenerCollector;
use Testo\Common\PluginConfigurator;
use Testo\Core\Context\TestInfo;
use Testo\Event\Framework\SessionFinished;
use Testo\Event\Framework\SessionStarting;
use Testo\Event\Test\TestBatchFinished;
use Testo\Event\Test\TestBatchStarting;
use Testo\Event\Test\TestDataSetFinished;
use Testo\Event\Test\TestPipelineFinished;
use Testo\Event\TestCase\TestCaseFinished;
use Testo\Event\TestCase\TestCaseStarting;
use Testo\Event\TestSuite\TestSuiteFinished;
use Testo\Event\TestSuite\TestSuiteStarting;
use Testo\Output\JUnit\Internal\JUnitInput;
use Testo\Output\JUnit\Internal\JUnitWriter;

/**
 * Plugin that emits a JUnit XML (PHPUnit dialect) report on session end.
 *
 * The XML is the de-facto interchange format for CI test reporters (GitHub
 * Actions, GitLab, Jenkins, Azure DevOps): adding this plugin to
 * `ApplicationConfig::plugins` lets those platforms render the test tab,
 * annotate failures on PR diffs, and track flaky tests without parsing
 * stdout.
 *
 * The conventional output filename is `junit.xml`; Infection in particular
 * expects it next to the `coverage-xml/` directory produced by
 * {@see \Testo\Codecov\Report\PhpUnitXmlReport} (e.g. `build/coverage/junit.xml`).
 *
 * # How activation works
 *
 * The plugin is already part of {@see \Testo\Application\Config\Plugin\ApplicationPlugins::defaults()},
 * so every project that doesn't replace the defaults gets one **inert** copy
 * for free. Inert means: no listeners are registered and no file is written.
 * The inert default exists so the `--log-junit=<path>` CLI flag has something to
 * activate without any change to `testo.php`.
 *
 * Activation rules:
 * - Constructor path is set → the plugin always writes to that path; the
 *   `--log-junit` flag is ignored. Use this when you want JUnit output as part
 *   of every run (e.g. on CI by default).
 * - Constructor path is null → the plugin reads the `--log-junit=<path>` flag
 *   on each run; if the flag is absent it stays inert. This is the mode
 *   used by the default inert instance and by Infection (which passes the
 *   path it expects via the flag).
 *
 * Multiple instances are fully independent — each one runs through the rules
 * above on its own. A plugin instance with a constructor path will produce
 * its file on **every** run, regardless of whether `--log-junit=…` was passed.
 * This means manually-added instances write **additional** report files,
 * they do not replace the default one. So this config:
 *
 * ```
 * ApplicationPlugins::with(new JUnitPlugin('/always.xml'))
 * ```
 *
 * yields one report at `/always.xml` on every run; if the same run is also
 * invoked with `--log-junit=/from-cli.xml`, the default inert instance writes
 * a second report at `/from-cli.xml` — both files end up on disk.
 *
 * To pin the report to a fixed path and prevent the CLI flag from creating
 * a second file, drop the default first:
 * `ApplicationPlugins::without(JUnitPlugin::class)->with(new JUnitPlugin('/always.xml'))`.
 *
 * @api
 */
final class JUnitPlugin implements PluginConfigurator
{
    /**
     * Tracks whether we're inside a DataProvider batch, keyed by test
     * definition object hash. Same guard `TeamcityPlugin` uses to avoid
     * emitting both the per-dataset and the rolled-up `<testcase>`.
     *
     * @var array<non-empty-string, bool>
     */
    private array $isBatch = [];

    /**
     * Output path. Seeded from the constructor argument and, if that was
     * absent, from the `--log-junit=<path>` CLI flag in {@see configure()}.
     * Stays null when neither source provided a path — in that case the
     * plugin remains inert.
     */
    private ?Path $resolvedPath;

    /**
     * Single-shot guard so the plugin attaches its listeners only to the
     * outer dispatcher when the same instance is shared across containers.
     * `ApplicationPlugins::defaults()` reuses one `JUnitPlugin` for every
     * top-level run AND for every nested {@see \Testo\Testing\Traits\TestRunner::runTest()}
     * sub-run; without this guard inner `SessionStarting` would clear the
     * outer writer state, and inner `SessionFinished` would overwrite the
     * file with inner-only suites.
     */
    private bool $configured = false;

    private readonly JUnitWriter $writer;

    /**
     * @param non-empty-string|null $outputPath Where to write the JUnit XML.
     *        When set, the plugin always writes to this path. When null, the
     *        plugin falls back to the `--log-junit=<path>` CLI flag, and stays
     *        inert if the flag is also absent.
     * @param non-empty-string $rootName Value of the `name` attribute on the
     *        root `<testsuites>` element. CI reporters display it as the title
     *        of the run; useful for distinguishing multiple JUnit reports
     *        produced by the same pipeline (e.g. `'Unit'` vs `'Integration'`).
     */
    public function __construct(
        ?string $outputPath = null,
        private readonly string $rootName = 'Testo',
    ) {
        $this->resolvedPath = $outputPath !== null && $outputPath !== ''
            ? Path::create($outputPath)
            : null;
        $this->writer = new JUnitWriter();
    }

    #[\Override]
    public function configure(Container $container): void
    {
        if ($this->configured) {
            return;
        }
        $this->configured = true;

        // Constructor path wins. CLI flag is consulted only when no explicit
        // path was passed to the constructor — that's how the inert default
        // instance in ApplicationPlugins::defaults() gets activated.
        if ($this->resolvedPath === null) {
            $cliPath = $container->get(JUnitInput::class)->outputPath;
            if ($cliPath === null || $cliPath === '') {
                return;
            }
            $this->resolvedPath = Path::create($cliPath);
        }

        $listeners = $container->get(EventListenerCollector::class);

        // Framework events
        $listeners->addListener(SessionStarting::class, $this->onSessionStarting(...));
        $listeners->addListener(SessionFinished::class, $this->onSessionFinished(...));

        // TestSuite events
        $listeners->addListener(TestSuiteStarting::class, $this->onTestSuiteStarting(...));
        $listeners->addListener(TestSuiteFinished::class, $this->onTestSuiteFinished(...));

        // TestCase events
        $listeners->addListener(TestCaseStarting::class, $this->onTestCaseStarting(...));
        $listeners->addListener(TestCaseFinished::class, $this->onTestCaseFinished(...));

        // Test Batch events (for DataProvider)
        $listeners->addListener(TestBatchStarting::class, $this->onTestBatchStarting(...));
        $listeners->addListener(TestBatchFinished::class, $this->onTestBatchFinished(...));

        // DataSet events (for individual datasets within DataProvider)
        $listeners->addListener(TestDataSetFinished::class, $this->onTestDataSetFinished(...));

        // Test Pipeline events (final event in the test lifecycle)
        $listeners->addListener(TestPipelineFinished::class, $this->onTestPipelineFinished(...));
    }

    /**
     * @return non-empty-string
     */
    private static function getId(TestInfo $testInfo): string
    {
        return \spl_object_hash($testInfo->testDefinition);
    }

    private static function formatDatasetSuffix(string|int $datasetKey, ?int $providerIndex): string
    {
        return $providerIndex === null
            ? (string) $datasetKey
            : "{$providerIndex}:{$datasetKey}";
    }

    private function onSessionStarting(SessionStarting $event): void
    {
        $this->writer->reset();
        $this->isBatch = [];
    }

    private function onSessionFinished(SessionFinished $event): void
    {
        \assert($this->resolvedPath !== null);
        $this->writer->write($this->resolvedPath, $this->rootName);
    }

    private function onTestSuiteStarting(TestSuiteStarting $event): void
    {
        $this->writer->startSuite($event->suiteInfo->name);
    }

    private function onTestSuiteFinished(TestSuiteFinished $event): void
    {
        $this->writer->finishSuite();
    }

    private function onTestCaseStarting(TestCaseStarting $event): void
    {
        // For class-bound cases, emit the bare FQN as the suite name (no
        // `[type]` suffix) and tag it with the class file. This matches
        // PHPUnit's JUnit shape and is what Infection's `JUnitTestFileDataProvider`
        // looks up via `//testsuite[@name="FQN"]`. Free-function cases keep
        // the human-readable `caseInfo->name` and have no source file.
        $caseInfo = $event->caseInfo;
        $reflection = $caseInfo->definition->reflection;
        $name = $reflection?->getName() ?? $caseInfo->name;
        \assert($name !== '');

        $file = $reflection?->getFileName();
        $file = ($file === false || $file === null || $file === '') ? null : $file;

        $this->writer->startSuite($name, $file);
    }

    private function onTestCaseFinished(TestCaseFinished $event): void
    {
        $this->writer->finishSuite();
    }

    private function onTestBatchStarting(TestBatchStarting $event): void
    {
        $id = self::getId($event->testInfo);
        $this->isBatch[$id] = true;
    }

    private function onTestBatchFinished(TestBatchFinished $event): void
    {
        // Marker is cleared in TestPipelineFinished, which fires after this.
    }

    private function onTestDataSetFinished(TestDataSetFinished $event): void
    {
        $name = $event->testResult->info->name . ' [' . self::formatDatasetSuffix($event->datasetKey, $event->providerIndex) . ']';
        \assert($name !== '');

        $this->writer->addTestResult($event->testResult, $name);
    }

    private function onTestPipelineFinished(TestPipelineFinished $event): void
    {
        $id = self::getId($event->testInfo);
        if (isset($this->isBatch[$id])) {
            // DataProvider test — individual datasets were already emitted.
            unset($this->isBatch[$id]);
            return;
        }

        $this->writer->addTestResult($event->testResult);
    }
}
