<?php

declare(strict_types=1);

namespace Tests\Output\Unit\JUnit;

use Internal\Container\ObjectContainer;
use Testo\Application\Internal\EventDispatcher;
use Testo\Assert;
use Testo\Common\EventListenerCollector;
use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\CaseResult;
use Testo\Core\Context\RunResult;
use Testo\Core\Context\SuiteInfo;
use Testo\Core\Context\SuiteResult;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Definition\CaseDefinition;
use Testo\Core\Definition\CaseDefinitions;
use Testo\Core\Definition\TestDefinition;
use Testo\Core\Value\Status;
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
use Testo\Output\JUnit\JUnitPlugin;
use Testo\Test;
use Tests\Output\Stub\JUnit\SampleTestClass;

#[Test]
final class JUnitPluginTest
{
    public function writesXmlOnSessionFinished(): void
    {
        // Arrange
        $path = self::tmpPath();
        try {
            $dispatcher = self::wirePlugin(new JUnitPlugin($path, 'Testo'));

            // Act — minimal lifecycle: session start/end with no tests.
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(self::sessionFinished());

            // Assert
            Assert::true(\file_exists($path));
            $xml = \simplexml_load_file($path);
            Assert::notSame($xml, false);
            Assert::same((string) $xml['name'], 'Testo');
            Assert::same((string) $xml['tests'], '0');
        } finally {
            self::cleanup($path);
        }
    }

    public function emitsCaseForRegularPipelineTest(): void
    {
        // Arrange
        $path = self::tmpPath();
        try {
            $dispatcher = self::wirePlugin(new JUnitPlugin($path));

            $suiteInfo = self::makeSuiteInfo('CoreSuite');
            $caseInfo = self::makeCaseInfo();
            $testInfo = self::makeTestInfo('passingTest');
            $result = new TestResult(info: $testInfo, status: Status::Passed, attributes: ['duration' => 7]);

            // Act
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(new TestSuiteStarting($suiteInfo));
            $dispatcher->dispatch(new TestCaseStarting($caseInfo));
            $dispatcher->dispatch(new TestPipelineFinished($testInfo, $result));
            $dispatcher->dispatch(new TestCaseFinished($caseInfo, new CaseResult([$result], Status::Passed)));
            $dispatcher->dispatch(new TestSuiteFinished($suiteInfo, new SuiteResult([], Status::Passed)));
            $dispatcher->dispatch(self::sessionFinished());

            // Assert
            $xml = \simplexml_load_file($path);
            Assert::notSame($xml, false);
            Assert::same((string) $xml['tests'], '1');
            Assert::same((string) $xml->testsuite['name'], 'CoreSuite');
            // Class-layer suite carries the bare FQN (Infection-compatible),
            // not `caseInfo->name` which has the `[type]` suffix.
            Assert::same((string) $xml->testsuite->testsuite['name'], SampleTestClass::class);
            Assert::same((string) $xml->testsuite->testsuite['file'], (new \ReflectionClass(SampleTestClass::class))->getFileName());
            Assert::same((string) $xml->testsuite->testsuite->testcase['name'], 'passingTest');
        } finally {
            self::cleanup($path);
        }
    }

    public function dataProviderEmitsCasePerDataSetAndSuppressesPipelineCase(): void
    {
        // Arrange
        $path = self::tmpPath();
        try {
            $dispatcher = self::wirePlugin(new JUnitPlugin($path));

            $suiteInfo = self::makeSuiteInfo('CoreSuite');
            $caseInfo = self::makeCaseInfo();
            $testInfo = self::makeTestInfo('passingTest');

            $datasetA = new TestResult(info: $testInfo, status: Status::Passed, attributes: ['duration' => 1]);
            $datasetB = new TestResult(info: $testInfo, status: Status::Passed, attributes: ['duration' => 2]);
            $aggregate = new TestResult(info: $testInfo, status: Status::Passed);

            // Act — TestBatchStarting marks the test as a data-provider parent;
            // TestPipelineFinished must skip emission so we don't double-count.
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(new TestSuiteStarting($suiteInfo));
            $dispatcher->dispatch(new TestCaseStarting($caseInfo));
            $dispatcher->dispatch(new TestBatchStarting($testInfo));
            $dispatcher->dispatch(new TestDataSetFinished($testInfo, $datasetA, 'alpha', null, 0));
            $dispatcher->dispatch(new TestDataSetFinished($testInfo, $datasetB, 'beta', null, 1));
            $dispatcher->dispatch(new TestBatchFinished($testInfo, $aggregate));
            $dispatcher->dispatch(new TestPipelineFinished($testInfo, $aggregate));
            $dispatcher->dispatch(new TestCaseFinished($caseInfo, new CaseResult([$aggregate], Status::Passed)));
            $dispatcher->dispatch(new TestSuiteFinished($suiteInfo, new SuiteResult([], Status::Passed)));
            $dispatcher->dispatch(self::sessionFinished());

            // Assert — exactly two cases, no rolled-up duplicate.
            $xml = \simplexml_load_file($path);
            Assert::notSame($xml, false);
            Assert::same((string) $xml['tests'], '2');
            $cases = $xml->testsuite->testsuite->testcase;
            Assert::count($cases, 2);
            Assert::same((string) $cases[0]['name'], 'passingTest [alpha]');
            Assert::same((string) $cases[1]['name'], 'passingTest [beta]');
        } finally {
            self::cleanup($path);
        }
    }

    public function multipleProvidersIncludeProviderIndexInName(): void
    {
        // Arrange
        $path = self::tmpPath();
        try {
            $dispatcher = self::wirePlugin(new JUnitPlugin($path));

            $suiteInfo = self::makeSuiteInfo('CoreSuite');
            $caseInfo = self::makeCaseInfo();
            $testInfo = self::makeTestInfo('passingTest');
            $result = new TestResult(info: $testInfo, status: Status::Passed);
            $aggregate = new TestResult(info: $testInfo, status: Status::Passed);

            // Act
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(new TestSuiteStarting($suiteInfo));
            $dispatcher->dispatch(new TestCaseStarting($caseInfo));
            $dispatcher->dispatch(new TestBatchStarting($testInfo));
            $dispatcher->dispatch(new TestDataSetFinished($testInfo, $result, 'k', 0, 0));
            $dispatcher->dispatch(new TestDataSetFinished($testInfo, $result, 'k', 1, 1));
            $dispatcher->dispatch(new TestBatchFinished($testInfo, $aggregate));
            $dispatcher->dispatch(new TestPipelineFinished($testInfo, $aggregate));
            $dispatcher->dispatch(new TestCaseFinished($caseInfo, new CaseResult([$aggregate], Status::Passed)));
            $dispatcher->dispatch(new TestSuiteFinished($suiteInfo, new SuiteResult([], Status::Passed)));
            $dispatcher->dispatch(self::sessionFinished());

            // Assert
            $xml = \simplexml_load_file($path);
            Assert::notSame($xml, false);
            $cases = $xml->testsuite->testsuite->testcase;
            Assert::same((string) $cases[0]['name'], 'passingTest [0:k]');
            Assert::same((string) $cases[1]['name'], 'passingTest [1:k]');
        } finally {
            self::cleanup($path);
        }
    }

    public function constructorPathWinsOverCliFlag(): void
    {
        // Arrange — manually-added instances must obey their explicit path,
        // ignoring `--log-junit=…` (which is intended for the default inert instance).
        $constructorPath = self::tmpPath();
        $cliPath = self::tmpPath();
        try {
            $dispatcher = self::wirePlugin(new JUnitPlugin($constructorPath), cliPath: $cliPath);

            // Act
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(self::sessionFinished());

            // Assert — constructor path is written; CLI path is untouched.
            Assert::true(\file_exists($constructorPath));
            Assert::false(\file_exists($cliPath));
        } finally {
            self::cleanup($constructorPath);
            self::cleanup($cliPath);
        }
    }

    public function cliFlagActivatesInertInstance(): void
    {
        // Arrange — no constructor path (inert default); CLI flag should activate it.
        $cliPath = self::tmpPath();
        try {
            $dispatcher = self::wirePlugin(new JUnitPlugin(), cliPath: $cliPath);

            // Act
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(self::sessionFinished());

            // Assert
            Assert::true(\file_exists($cliPath));
        } finally {
            self::cleanup($cliPath);
        }
    }

    public function inertWithoutPathFromAnySource(): void
    {
        // Arrange — no constructor path, no CLI path → plugin must not register listeners.
        $dispatcher = self::wirePlugin(new JUnitPlugin());

        // Act — drive a full session; nothing should blow up and no file should appear.
        $dispatcher->dispatch(new SessionStarting());
        $dispatcher->dispatch(self::sessionFinished());

        // Assert — listener registry is empty for our events; absence of crash already implies no-op.
        Assert::same(\iterator_to_array($dispatcher->getListenersForEvent(new SessionStarting()), false), []);
        Assert::same(\iterator_to_array($dispatcher->getListenersForEvent(self::sessionFinished()), false), []);
    }

    public function sessionStartingResetsBetweenRuns(): void
    {
        // Arrange — emit two sessions through the same plugin instance; the
        // second run must not contain residue from the first.
        $path = self::tmpPath();
        try {
            $plugin = new JUnitPlugin($path);
            $dispatcher = self::wirePlugin($plugin);

            $suiteInfo = self::makeSuiteInfo('Run1');
            $caseInfo = self::makeCaseInfo();
            $testInfo = self::makeTestInfo('passingTest');

            // Run 1
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(new TestSuiteStarting($suiteInfo));
            $dispatcher->dispatch(new TestCaseStarting($caseInfo));
            $dispatcher->dispatch(new TestPipelineFinished(
                $testInfo,
                new TestResult($testInfo, Status::Passed),
            ));
            $dispatcher->dispatch(new TestCaseFinished($caseInfo, new CaseResult([], Status::Passed)));
            $dispatcher->dispatch(new TestSuiteFinished($suiteInfo, new SuiteResult([], Status::Passed)));
            $dispatcher->dispatch(self::sessionFinished());

            // Run 2 — fresh, but only one suite "Run2" with no cases.
            $suiteInfo2 = self::makeSuiteInfo('Run2');
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(new TestSuiteStarting($suiteInfo2));
            $dispatcher->dispatch(new TestSuiteFinished($suiteInfo2, new SuiteResult([], Status::Passed)));
            $dispatcher->dispatch(self::sessionFinished());

            // Act
            $xml = \simplexml_load_file($path);

            // Assert — only Run2 is present, totals are zero.
            Assert::notSame($xml, false);
            Assert::same((string) $xml['tests'], '0');
            Assert::same((string) $xml->testsuite['name'], 'Run2');
            Assert::count($xml->testsuite, 1);
        } finally {
            self::cleanup($path);
        }
    }

    /**
     * Wires a freshly built plugin into a real {@see EventDispatcher}.
     */
    private static function wirePlugin(JUnitPlugin $plugin, ?string $cliPath = null): EventDispatcher
    {
        $dispatcher = new EventDispatcher();
        $container = new ObjectContainer();
        $container->set($dispatcher, EventListenerCollector::class);

        $input = new JUnitInput();
        $input->outputPath = $cliPath;
        $container->set($input, JUnitInput::class);

        $plugin->configure($container);

        return $dispatcher;
    }

    private static function sessionFinished(): SessionFinished
    {
        return new SessionFinished(new RunResult([], Status::Passed, 0.0));
    }

    /**
     * @param non-empty-string $name
     */
    private static function makeSuiteInfo(string $name): SuiteInfo
    {
        return new SuiteInfo(name: $name, testCases: new CaseDefinitions());
    }

    private static function makeCaseInfo(): CaseInfo
    {
        return new CaseInfo(
            definition: new CaseDefinition(
                name: SampleTestClass::class,
                type: 'test',
                reflection: new \ReflectionClass(SampleTestClass::class),
            ),
        );
    }

    /**
     * @param non-empty-string $method
     */
    private static function makeTestInfo(string $method): TestInfo
    {
        return new TestInfo(
            name: $method,
            caseInfo: self::makeCaseInfo(),
            testDefinition: new TestDefinition(new \ReflectionMethod(SampleTestClass::class, $method)),
        );
    }

    /**
     * @return non-empty-string
     */
    private static function tmpPath(): string
    {
        return \sys_get_temp_dir() . '/testo_junit_plugin_' . \uniqid() . '.xml';
    }

    private static function cleanup(string $path): void
    {
        \is_file($path) and \unlink($path);
        $dir = \dirname($path);
        // Don't blow up on nested temp dirs that other tests may share.
        if (\is_dir($dir) && $dir !== \sys_get_temp_dir()) {
            // Best-effort: only remove if empty.
            @\rmdir($dir);
        }
    }
}
