<?php

declare(strict_types=1);

namespace Testo\Output\Teamcity;

use Internal\Container\Container;
use Testo\Common\EventListenerCollector;
use Testo\Common\PluginConfigurator;
use Testo\Core\Context\TestInfo;
use Testo\Event\Framework\SessionStarting;
use Testo\Event\Message\MessageReceived;
use Testo\Event\Test\TestBatchFinished;
use Testo\Event\Test\TestBatchStarting;
use Testo\Event\Test\TestDataSetFinished;
use Testo\Event\Test\TestDataSetStarting;
use Testo\Event\Test\TestPipelineFinished;
use Testo\Event\Test\TestPipelineStarting;
use Testo\Event\TestCase\TestCaseFinished;
use Testo\Event\TestCase\TestCaseStarting;
use Testo\Event\TestSuite\TestSuiteFinished;
use Testo\Event\TestSuite\TestSuiteStarting;
use Testo\Output\Teamcity\Teamcity\TeamcityLogger;

final class TeamcityPlugin implements PluginConfigurator
{
    /**
     * Tracks whether we're inside a DataProvider batch.
     *
     * @var array<non-empty-string, bool>
     */
    private array $isBatch = [];

    /**
     * Name of the in-flight test/data set that streamed {@see MessageReceived} output is attributed
     * to. `null` when no test is running (output between tests is dropped).
     *
     * @var non-empty-string|null
     */
    private ?string $currentName = null;

    /**
     * A regular (non-DataProvider) test whose `testStarted` has not been emitted yet. It is emitted
     * lazily on the first message (so output can stream in real time), or at pipeline finish if the
     * test produced no output. `null` once `testStarted` has been emitted (or for data sets, which
     * emit it eagerly).
     */
    private ?TestInfo $pendingStart = null;

    private readonly TeamcityLogger $logger;

    public function __construct()
    {
        $this->logger = new TeamcityLogger();
    }

    #[\Override]
    public function configure(Container $container): void
    {
        $listeners = $container->get(EventListenerCollector::class);

        // Framework events
        $listeners->addListener(SessionStarting::class, $this->onSessionStarting(...));

        // Messenger output — streamed in real time as stdout/stderr for the current test.
        $listeners->addListener(MessageReceived::class, $this->onMessageReceived(...));

        // Test Pipeline events (lifecycle of entire test through all interceptors)
        $listeners->addListener(TestPipelineStarting::class, $this->onTestPipelineStarting(...));
        $listeners->addListener(TestPipelineFinished::class, $this->onTestPipelineFinished(...));

        // Test Batch events (for DataProvider)
        $listeners->addListener(TestBatchStarting::class, $this->onTestBatchStarting(...));
        $listeners->addListener(TestBatchFinished::class, $this->onTestBatchFinished(...));

        // DataSet events (for individual datasets within DataProvider)
        $listeners->addListener(TestDataSetStarting::class, $this->onTestDataSetStarting(...));
        $listeners->addListener(TestDataSetFinished::class, $this->onTestDataSetFinished(...));

        // TestCase events
        $listeners->addListener(TestCaseStarting::class, $this->onTestCaseStarting(...));
        $listeners->addListener(TestCaseFinished::class, $this->onTestCaseFinished(...));

        // TestSuite events
        $listeners->addListener(TestSuiteStarting::class, $this->onTestSuiteStarting(...));
        $listeners->addListener(TestSuiteFinished::class, $this->onTestSuiteFinished(...));
    }

    private static function getId(TestInfo $testInfo): string
    {
        return \spl_object_hash($testInfo->testDefinition);
    }

    /**
     * Clears the current-test attribution so output emitted outside any test is dropped.
     */
    private function resetCurrent(): void
    {
        $this->currentName = null;
        $this->pendingStart = null;
    }

    private function onSessionStarting(SessionStarting $event): void
    {
        $this->logger->logEnvironment();
    }

    private function onMessageReceived(MessageReceived $event): void
    {
        // No test in flight — output between tests is not attributable, so drop it.
        if ($this->currentName === null) {
            return;
        }

        // Lazily emit testStarted for a regular test on its first output, so it streams in real time.
        if ($this->pendingStart !== null) {
            $this->logger->testStartedFromInfo($this->pendingStart);
            $this->pendingStart = null;
        }

        $this->logger->logMessage($this->currentName, $event->message);
    }

    private function onTestPipelineStarting(TestPipelineStarting $event): void
    {
        // Assume a regular test: attribute output to it and keep testStarted pending until output
        // arrives. If it turns out to be a DataProvider batch, onTestBatchStarting clears this.
        $this->currentName = $event->testInfo->name;
        $this->pendingStart = $event->testInfo;
    }

    private function onTestPipelineFinished(TestPipelineFinished $event): void
    {
        // Check if this test was inside a DataProvider batch
        $id = self::getId($event->testInfo);
        if (isset($this->isBatch[$id])) {
            // DataProvider test - already handled in batch events
            unset($this->isBatch[$id]);
            $this->resetCurrent();
            return;
        }

        // Regular test: testStarted was emitted lazily on first output; if there was none, emit now.
        if ($this->pendingStart !== null) {
            $this->logger->testStartedFromInfo($this->pendingStart);
            $this->pendingStart = null;
        }

        $duration = (int) $event->testResult->getAttribute('duration');
        $this->logger->handleSingleTestResult($event->testResult, $duration);
        $this->resetCurrent();
    }

    private function onTestBatchStarting(TestBatchStarting $event): void
    {
        // Mark that we're inside a batch
        $id = self::getId($event->testInfo);
        $this->isBatch[$id] = true;

        // It's a DataProvider, not a single test: drop the pending single-test start; data sets
        // emit their own testStarted and own the current attribution.
        $this->resetCurrent();

        // For DataProvider tests, start a test suite (wraps all data sets)
        $this->logger->batchStartedFromInfo($event->testInfo);
    }

    private function onTestBatchFinished(TestBatchFinished $event): void
    {
        // For DataProvider tests, close the test suite
        $this->logger->batchFinishedFromInfo($event->testInfo);
    }

    private function onTestDataSetStarting(TestDataSetStarting $event): void
    {
        // Send testStarted for individual dataset within DataProvider
        $prefix = $event->providerIndex === null ? '' : "$event->providerIndex:";
        $locationSuffix = $event->providerIndex !== null
            ? ":$event->dataSetKey:$event->providerIndex"
            : ":$event->dataSetKey";
        $name = "Dataset #{$prefix}{$event->datasetIndex} [$event->dataSetKey]";
        $this->logger->testStartedFromInfo(
            $event->testInfo,
            overrideName: $name,
            locationSuffix: $locationSuffix,
        );

        // testStarted already emitted eagerly; stream this data set's output to it in real time.
        $this->currentName = $name;
        $this->pendingStart = null;
    }

    private function onTestDataSetFinished(TestDataSetFinished $event): void
    {
        // Handle individual dataset result
        $duration = (int) $event->testResult->getAttribute('duration');
        $prefix = $event->providerIndex === null ? '' : "$event->providerIndex:";
        $name = "Dataset #{$prefix}{$event->datasetIndex} [$event->datasetKey]";

        $this->logger->handleSingleTestResult($event->testResult, $duration, overrideName: $name);
        $this->resetCurrent();
    }

    private function onTestCaseStarting(TestCaseStarting $event): void
    {
        $this->logger->caseStartedFromInfo($event->caseInfo);
    }

    private function onTestCaseFinished(TestCaseFinished $event): void
    {
        $this->logger->handleCaseResult($event->caseInfo, $event->caseResult);
    }

    private function onTestSuiteStarting(TestSuiteStarting $event): void
    {
        $this->logger->suiteStartedFromInfo($event->suiteInfo);
    }

    private function onTestSuiteFinished(TestSuiteFinished $event): void
    {
        $this->logger->handleSuiteResult($event->suiteInfo, $event->suiteResult);
    }
}
