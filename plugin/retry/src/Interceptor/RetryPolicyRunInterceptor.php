<?php

declare(strict_types=1);

namespace Testo\Retry\Interceptor;

use Psr\EventDispatcher\EventDispatcherInterface;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Log\Level;
use Testo\Core\Value\Status;
use Testo\Event\Test\TestRetrying;
use Testo\Messenger;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;
use Testo\Pipeline\Policy\ConflictPolicy;
use Testo\Retry;

/**
 * Interceptor that retries a test execution based on the provided retry policy.
 *
 * @see Retry
 *
 * @api
 */
#[InterceptorOptions(order: InterceptorOptions::ORDER_DEFAULT - 200, onConflict: ConflictPolicy::Last)]
final readonly class RetryPolicyRunInterceptor implements TestRunInterceptor
{
    /**
     * Channel the breadcrumbs for discarded (retried) attempts are written to.
     */
    public const CHANNEL = 'retry';

    public function __construct(
        private Retry $options,
        private EventDispatcherInterface $eventDispatcher,
        private Messenger $messenger,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $attempts = $this->options->maxAttempts;
        $isFlaky = false;

        $attempt = 1;
        run:
        --$attempts;
        /**
         * @var TestResult $result
         * @var callable $commit Persist messages from the test
         */
        [$result, $commit] = $this->messenger->fork(
            static fn(callable $commit): array => [$next($info), $commit],
            holdEvents: true,
        );

        if ($result->status->isFailure()) {
            # Test failed, check if we can retry
            if ($attempts > 0) {
                # The attempt's own output went into the dropped fork; leave a breadcrumb in the
                # parent scope so the discarded attempt still leaves a trace in the test's output.
                $this->messenger->log(
                    self::CHANNEL,
                    $result->failure === null
                        ? "Attempt $attempt failed.\n"
                        : "Attempt $attempt failed: {$result->failure->getMessage()}\n",
                    Level::Warning,
                );

                $isFlaky = true;
                $this->eventDispatcher->dispatch(
                    new TestRetrying($info, ++$attempt, $result),
                );
                goto run;
            }

            $commit();
            return $result;
        }

        $commit();
        return $isFlaky && $this->options->markFlaky && $result->status->isSuccessful()
            ? $result->with(status: Status::Flaky)
            : $result;
    }
}
