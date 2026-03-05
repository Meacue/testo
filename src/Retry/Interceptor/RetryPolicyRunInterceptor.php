<?php

declare(strict_types=1);

namespace Testo\Retry\Interceptor;

use Psr\EventDispatcher\EventDispatcherInterface;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\Status;
use Testo\Core\Value\TestType;
use Testo\Event\Test\TestRetrying;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;
use Testo\Retry\RetryPolicy;

/**
 * Interceptor that retries a test execution based on the provided retry policy.
 *
 * @see RetryPolicy
 */
#[InterceptorOptions(order: InterceptorOptions::ORDER_DEFAULT)]
final class RetryPolicyRunInterceptor implements TestRunInterceptor
{
    public function __construct(
        private readonly RetryPolicy $options,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $attempts = $this->options->maxAttempts;
        $isFlaky = false;

        run:
        --$attempts;
        $attempt = 1;
        /** @var TestResult $result */
        $result = $next($info);

        if ($result->status->isFailure()) {
            # Test failed, check if we can retry
            if ($attempts > 0) {
                $isFlaky = true;
                $this->eventDispatcher->dispatch(
                    new TestRetrying($info, ++$attempt, $result),
                );
                goto run;
            }

            return $result;
        }

        return $isFlaky && $this->options->markFlaky && $result->status->isSuccessful()
            ? $result->with(status: Status::Flaky)
            : $result;
    }
}
