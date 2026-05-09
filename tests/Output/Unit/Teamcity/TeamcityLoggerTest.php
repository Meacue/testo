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
        $logger = new TeamcityLogger();
        $result = self::makeFailedResult(self::makeComparisonFailure());

        $output = self::capture(static fn() => $logger->handleSingleTestResult($result));

        Assert::string($output)->contains("type='comparisonFailure'");
        Assert::string($output)->contains("expected='Array");
        Assert::string($output)->contains("actual='Array");
    }

    public function testFailedFromResultEmitsComparisonFailureAttributesForComparisonFailure(): void
    {
        $logger = new TeamcityLogger();
        $result = self::makeFailedResult(self::makeComparisonFailure());

        $output = self::capture(static fn() => $logger->testFailedFromResult($result));

        Assert::string($output)->contains("type='comparisonFailure'");
        Assert::string($output)->contains("expected='Array");
        Assert::string($output)->contains("actual='Array");
    }

    public function handleSingleTestResultOmitsComparisonAttributesForGenericFailure(): void
    {
        $logger = new TeamcityLogger();
        $failure = new AssertionException(
            value: '"foo"',
            assertion: 'is blank',
            context: '',
            reason: 'value contains data',
            details: '',
        );
        $result = self::makeFailedResult($failure);

        $output = self::capture(static fn() => $logger->handleSingleTestResult($result));

        Assert::string($output)->notContains("type='comparisonFailure'");
        Assert::string($output)->notContains("expected='");
        Assert::string($output)->notContains("actual='");
    }

    private static function capture(\Closure $callback): string
    {
        \ob_start();
        try {
            $callback();
        } finally {
            $output = \ob_get_clean();
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
        $reflection = new \ReflectionMethod(SampleTestClass::class, 'failingTest');

        $info = new TestInfo(
            name: 'failingTest',
            caseInfo: new CaseInfo(
                definition: new CaseDefinition(
                    name: SampleTestClass::class,
                    type: 'test',
                    reflection: new \ReflectionClass(SampleTestClass::class),
                ),
            ),
            testDefinition: new TestDefinition($reflection),
        );

        return new TestResult(
            info: $info,
            status: Status::Failed,
            failure: $failure,
            attributes: ['duration' => 0],
        );
    }
}
