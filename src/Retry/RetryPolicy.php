<?php

declare(strict_types=1);

namespace Testo\Retry;

use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Retry\Interceptor\RetryPolicyRunInterceptor;

/**
 * Retry test on failure.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_CLASS)]
#[FallbackInterceptor(RetryPolicyRunInterceptor::class)]
final class RetryPolicy implements Interceptable
{
    public function __construct(
        /**
         * Maximum number of attempts.
         */
        public readonly int $maxAttempts = 3,

        /**
         * Mark the test as flaky if it passed on retry.
         */
        public readonly bool $markFlaky = true,
    ) {}
}
