<?php

declare(strict_types=1);

namespace Testo\Pipeline;

/**
 * Father interface for all interceptors.
 *
 * You can use the interface to build your own pipeline.
 *
 * @template TInput
 * @template-covariant TOutput
 *
 * @api
 */
interface Interceptor {}
