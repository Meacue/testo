<?php

declare(strict_types=1);

namespace Testo\Pipeline;

use Testo\Pipeline\Internal\InterceptorMarker;

/**
 * Provides API to configure {@see InterceptorProvider}.
 */
interface InterceptorCollector
{
    /**
     * Add interceptor to the pipeline.
     *
     * @param InterceptorMarker|class-string<InterceptorMarker> $interceptor Interceptor instance or class name.
     */
    public function addInterceptor(InterceptorMarker|string $interceptor): void;
}
