<?php

declare(strict_types=1);

namespace Testo\Pipeline\Attribute;

use Testo\Pipeline\Interceptor;

/**
 * Define a fallback interceptor for the {@see Interceptable} attribute.
 *
 * For example:
 *
 * ```
 *  #[\Attribute]
 *  #[FallbackInterceptor(RetryPolicyCallInterceptor::class)]
 *  final class RetryPolicy {}
 * ```
 *
 * Makes sense only for interceptors that are executed during tests execution.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class FallbackInterceptor
{
    public function __construct(
        /**
         * Interceptor class that can handle the attribute.
         *
         * @var class-string<\Testo\Pipeline\Interceptor>
         */
        public readonly string $class,
    ) {}
}
