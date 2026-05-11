<?php

declare(strict_types=1);

namespace Testo\Repeat\Internal;

use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\Status;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;
use Testo\Pipeline\Policy\ConflictPolicy;
use Testo\Repeat;

/**
 * Interceptor that repeats a test execution based on the provided repeat policy.
 *
 * @see Repeat
 *
 * @api
 */
#[InterceptorOptions(order: InterceptorOptions::ORDER_DEFAULT - 190, onConflict: ConflictPolicy::Last)]
final readonly class RepeatInterceptor implements TestRunInterceptor
{
    public function __construct(
        private Repeat $options,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $times = $this->options->times;
        $maxFailures = $this->options->maxFailures;

        $failures = 0;

        do {
            /** @var TestResult $result */
            $result = $next($info);

            // Skipped / Cancelled / Aborted — abort immediately regardless of threshold.
            if (!$result->status->isCompleted()) {
                return $result;
            }

            if ($result->status->isFailure() && ++$failures > $maxFailures) {
                return $result;
            }
        } while (--$times > 0);

        return $failures > 0 && $this->options->markFlaky
            ? $result->with(status: Status::Flaky)
            : $result;
    }
}
