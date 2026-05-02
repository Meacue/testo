<?php

declare(strict_types=1);

namespace Testo\Pipeline;

/**
 * Provides API to configure {@see InterceptorProvider}.
 *
 * @api
 */
interface InterceptorCollector
{
    /**
     * Add interceptor to the pipeline.
     *
     * @param Interceptor|class-string<Interceptor> $interceptor Interceptor instance or class name.
     */
    public function addInterceptor(Interceptor|string $interceptor): void;
}
