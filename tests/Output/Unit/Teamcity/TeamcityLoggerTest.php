<?php

declare(strict_types=1);

namespace Tests\Output\Unit\Teamcity;

use Testo\Assert;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Assert\State\Assertion\ComparisonFailure;
use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Definition\CaseDefinition;
use Testo\Core\Definition\TestDefinition;
use Testo\Core\Value\Status;
use Testo\Output\Teamcity\Teamcity\TeamcityLogger;
use Testo\Test;
use Tests\Output\Stub\Teamcity\SampleTestClass;

#[Test]
final class TeamcityLoggerTest
{
    public function handleSingleTestResultEmitsComparisonFailureAttributesForComparisonFailure(): void
    {
        $result = self::makeFailedResult(self::makeComparisonFailure());

        $output = self::capture(static fn(TeamcityLogger $logger) => $logger->handleSingleTestResult($result));

        Assert::string($output)->contains("type='comparisonFailure'");
        Assert::string($output)->contains("expected='Array");
        Assert::string($output)->contains("actual='Array");
    }

    public function testFailedFromResultEmitsComparisonFailureAttributesForComparisonFailure(): void
    {
        $result = self::makeFailedResult(self::makeComparisonFailure());

        $output = self::capture(static fn(TeamcityLogger $logger) => $logger->testFailedFromResult($result));

        Assert::string($output)->contains("type='comparisonFailure'");
        Assert::string($output)->contains("expected='Array");
        Assert::string($output)->contains("actual='Array");
    }

    public function handleSingleTestResultOmitsComparisonAttributesForGenericFailure(): void
    {
        $failure = new AssertionException(
            value: '"foo"',
            assertion: 'is blank',
            context: '',
            reason: 'value contains data',
            details: '',
        );
        $result = self::makeFailedResult($failure);

        $output = self::capture(static fn(TeamcityLogger $logger) => $logger->handleSingleTestResult($result));

        Assert::string($output)->notContains("type='comparisonFailure'");
        Assert::string($output)->notContains("expected='");
        Assert::string($output)->notContains("actual='");
    }

    public function testStartedFromInfoEmitsDescriptionFromPhpDoc(): void
    {
        $info = self::makeInfo('describedTest');

        $output = self::capture(static fn(TeamcityLogger $logger) => $logger->testStartedFromInfo($info));

        Assert::string($output)->contains("metainfo='Verifies the widget renders correctly.'");
    }

    public function testStartedFromInfoOmitsMetainfoWhenNoPhpDoc(): void
    {
        $info = self::makeInfo('passingTest');

        $output = self::capture(static fn(TeamcityLogger $logger) => $logger->testStartedFromInfo($info));

        Assert::string($output)->notContains('metainfo=');
    }

    /**
     * Runs the callback against a logger writing to an in-memory stream and returns what it wrote.
     *
     * The logger writes straight to its stream (bypassing output buffering), so capture is done by
     * injecting a `php://memory` stream rather than `ob_start()`.
     *
     * @param \Closure(TeamcityLogger): void $callback
     */
    private static function capture(\Closure $callback): string
    {
        $stream = \fopen('php://memory', 'rb+');
        \assert($stream !== false);

        try {
            $callback(new TeamcityLogger($stream));
            \rewind($stream);
            $output = \stream_get_contents($stream);
        } finally {
            \fclose($stream);
        }

        return $output === false ? '' : $output;
    }

    private static function makeComparisonFailure(): ComparisonFailure
    {
        return new ComparisonFailure(
            expected: ['line1', 'line2', 'line3'],
            actual: ['line1', 'line2_changed', 'line3'],
            value: 'array(3)',
            assertion: 'is the same as `array(3)`',
            context: '',
            reason: 'expected `array(3)`, got `array(3)`',
        );
    }

    private static function makeFailedResult(\Throwable $failure): TestResult
    {
        return new TestResult(
            info: self::makeInfo('failingTest'),
            status: Status::Failed,
            failure: $failure,
            attributes: ['duration' => 0],
        );
    }

    /**
     * @param non-empty-string $method Method of {@see SampleTestClass} backing the test definition.
     */
    private static function makeInfo(string $method): TestInfo
    {
        return new TestInfo(
            name: $method,
            caseInfo: new CaseInfo(
                definition: new CaseDefinition(
                    name: SampleTestClass::class,
                    type: 'test',
                    reflection: new \ReflectionClass(SampleTestClass::class),
                ),
            ),
            testDefinition: new TestDefinition(new \ReflectionMethod(SampleTestClass::class, $method)),
        );
    }
}
