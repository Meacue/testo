<?php

declare(strict_types=1);

namespace Testo\Module\Interceptor;

use Testo\Module\Interceptor\Policy\ConflictPolicy;

/**
 * Interceptor options.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class InterceptorOptions
{
    public const DEFAULT_ORDER = 0;

    public function __construct(
        /**
         * The priority of the interceptor.
         *
         * Lower priority interceptors are applied first in the interceptor chain.
         * Higher priority interceptors are closer to the test function in the interceptor chain.
         */
        public readonly int $order = self::DEFAULT_ORDER,
        public readonly ConflictPolicy $onConflict = ConflictPolicy::First,
    ) {}
}
