<?php

declare(strict_types=1);

namespace Tests\Retry\Unit;

use Psr\EventDispatcher\EventDispatcherInterface;
use Testo\Assert;
use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Definition\CaseDefinition;
use Testo\Core\Definition\TestDefinition;
use Testo\Core\Value\Status;
use Testo\Data\DataSet;
use Testo\Event\Test\TestRetrying;
use Testo\Retry;
use Testo\Retry\Interceptor\RetryPolicyRunInterceptor;
use Testo\Test;

#[Test]
final class RetryPolicyRunInterceptorTest
{
    public function noRetryWhenFirstAttemptPasses(): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(new Retry(maxAttempts: 3), $dispatcher);
        $info = self::createTestInfo();
        $callCount = 0;
        $next = static function (TestInfo $info) use (&$callCount): TestResult {
            $callCount++;
            return new TestResult(info: $info, status: Status::Passed);
        };

        $result = $interceptor->runTest($info, $next);

        Assert::same($callCount, 1);
        Assert::same($result->status, Status::Passed);
        Assert::same($dispatcher->dispatched, []);
    }

    public function failingTestExhaustsAllAttempts(): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(new Retry(maxAttempts: 3), $dispatcher);
        $info = self::createTestInfo();
        $callCount = 0;
        $next = static function (TestInfo $info) use (&$callCount): TestResult {
            $callCount++;
            return new TestResult(info: $info, status: Status::Failed);
        };

        $result = $interceptor->runTest($info, $next);

        Assert::same($callCount, 3);
        Assert::same($result->status, Status::Failed);
        Assert::same(\count($dispatcher->dispatched), 2);
    }

    public function passOnRetryMarksAsFlaky(): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(new Retry(maxAttempts: 3), $dispatcher);
        $info = self::createTestInfo();
        $callCount = 0;
        $next = static function (TestInfo $info) use (&$callCount): TestResult {
            $callCount++;
            return new TestResult(
                info: $info,
                status: $callCount === 1 ? Status::Failed : Status::Passed,
            );
        };

        $result = $interceptor->runTest($info, $next);

        Assert::same($callCount, 2);
        Assert::same($result->status, Status::Flaky);
        Assert::same(\count($dispatcher->dispatched), 1);
    }

    public function passOnRetryStaysPassedWhenMarkFlakyIsFalse(): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(
            new Retry(maxAttempts: 3, markFlaky: false),
            $dispatcher,
        );
        $info = self::createTestInfo();
        $callCount = 0;
        $next = static function (TestInfo $info) use (&$callCount): TestResult {
            $callCount++;
            return new TestResult(
                info: $info,
                status: $callCount === 1 ? Status::Failed : Status::Passed,
            );
        };

        $result = $interceptor->runTest($info, $next);

        Assert::same($callCount, 2);
        Assert::same($result->status, Status::Passed);
    }

    public function singleAttemptDoesNotRetry(): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(new Retry(maxAttempts: 1), $dispatcher);
        $info = self::createTestInfo();
        $callCount = 0;
        $next = static function (TestInfo $info) use (&$callCount): TestResult {
            $callCount++;
            return new TestResult(info: $info, status: Status::Failed);
        };

        $result = $interceptor->runTest($info, $next);

        Assert::same($callCount, 1);
        Assert::same($result->status, Status::Failed);
        Assert::same($dispatcher->dispatched, []);
    }

    /**
     * Only Failed and Error statuses should trigger a retry; everything else returns immediately.
     */
    #[DataSet([Status::Passed, false])]
    #[DataSet([Status::Risky, false])]
    #[DataSet([Status::Flaky, false])]
    #[DataSet([Status::Skipped, false])]
    #[DataSet([Status::Cancelled, false])]
    #[DataSet([Status::Aborted, false])]
    #[DataSet([Status::Failed, true])]
    #[DataSet([Status::Error, true])]
    public function statusTriggersRetry(Status $status, bool $shouldRetry): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(new Retry(maxAttempts: 3), $dispatcher);
        $info = self::createTestInfo();
        $callCount = 0;
        $next = static function (TestInfo $info) use (&$callCount, $status): TestResult {
            $callCount++;
            return new TestResult(info: $info, status: $status);
        };

        $interceptor->runTest($info, $next);

        Assert::same($callCount, $shouldRetry ? 3 : 1);
    }

    public function dispatchesTestRetryingEventPerRetry(): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(new Retry(maxAttempts: 4), $dispatcher);
        $info = self::createTestInfo();
        $next = static fn(TestInfo $info): TestResult => new TestResult(
            info: $info,
            status: Status::Failed,
        );

        $interceptor->runTest($info, $next);

        Assert::same(\count($dispatcher->dispatched), 3);
        foreach ($dispatcher->dispatched as $event) {
            Assert::true($event instanceof TestRetrying);
            Assert::same($event->testInfo, $info);
            Assert::same($event->attempt, 2);
        }
    }

    public function eventCarriesPreviousFailedResult(): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(new Retry(maxAttempts: 2), $dispatcher);
        $info = self::createTestInfo();
        $iteration = 0;
        $next = static function (TestInfo $info) use (&$iteration): TestResult {
            $iteration++;
            return new TestResult(info: $info, status: Status::Failed, result: $iteration);
        };

        $interceptor->runTest($info, $next);

        Assert::same(\count($dispatcher->dispatched), 1);
        $event = $dispatcher->dispatched[0];
        Assert::true($event instanceof TestRetrying);
        Assert::same($event->previousRunResult->status, Status::Failed);
        Assert::same($event->previousRunResult->result, 1);
    }

    public function passesTestInfoToNext(): void
    {
        $dispatcher = self::createDispatcher();
        $interceptor = new RetryPolicyRunInterceptor(new Retry(maxAttempts: 2), $dispatcher);
        $info = self::createTestInfo();
        $receivedInfos = [];
        $next = static function (TestInfo $receivedInfo) use (&$receivedInfos): TestResult {
            $receivedInfos[] = $receivedInfo;
            return new TestResult(info: $receivedInfo, status: Status::Failed);
        };

        $interceptor->runTest($info, $next);

        Assert::same(\count($receivedInfos), 2);
        Assert::same($receivedInfos[0], $info);
        Assert::same($receivedInfos[1], $info);
    }

    private static function createDispatcher(): EventDispatcherInterface
    {
        return new class() implements EventDispatcherInterface {
            /** @var list<object> */
            public array $dispatched = [];

            #[\Override]
            public function dispatch(object $event): object
            {
                $this->dispatched[] = $event;
                return $event;
            }
        };
    }

    private static function createTestInfo(): TestInfo
    {
        $reflection = new \ReflectionMethod(self::class, 'createTestInfo');
        $caseDefinition = new CaseDefinition(name: 'TestCase', type: 'test');
        $caseInfo = new CaseInfo(definition: $caseDefinition);
        $testDefinition = new TestDefinition(reflection: $reflection);

        return new TestInfo(
            name: 'testMethod',
            caseInfo: $caseInfo,
            testDefinition: $testDefinition,
        );
    }
}
