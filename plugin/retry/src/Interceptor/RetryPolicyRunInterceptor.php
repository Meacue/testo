<?php

declare(strict_types=1);

namespace Testo\Retry\Interceptor;

use Psr\EventDispatcher\EventDispatcherInterface;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
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

    private Messenger\Channel $channel;

    public function __construct(
        private Retry $options,
        private EventDispatcherInterface $eventDispatcher,
        Messenger $messenger,
    ) {
        $this->channel = $messenger->channel(self::CHANNEL);
    }

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $attempts = $this->options->maxAttempts;
        $isFlaky = false;

        $attempt = 1;
        run:
        --$attempts;
        /** @var TestResult $result */
        $result = $next($info);

        if ($result->status->isFailure()) {
            # Test failed, check if we can retry
            if ($attempts > 0) {
                # The attempt's own output went into the dropped fork; leave a breadcrumb in the
                # parent scope so the discarded attempt still leaves a trace in the test's output.
                $this->channel->warning(
                    $result->failure === null
                        ? "Attempt $attempt failed.\n"
                        : "Attempt $attempt failed: {$result->failure->getMessage()}\n",
                );

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
