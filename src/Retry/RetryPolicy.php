<?php

declare(strict_types=1);

namespace Testo\Retry;

use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Retry\Interceptor\RetryPolicyRunInterceptor;

/**
 * Retry test on failure.
 *
 * A universal retry policy that can be applied to any test.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_CLASS)]
#[FallbackInterceptor(RetryPolicyRunInterceptor::class)]
final readonly class RetryPolicy implements Interceptable
{
    public function __construct(
        /**
         * Maximum number of attempts.
         */
        public int $maxAttempts = 3,

        /**
         * Mark the test as flaky if it passed on retry.
         */
        public bool $markFlaky = true,
    ) {}
}
