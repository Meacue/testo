<?php

declare(strict_types=1);

namespace Testo\Sample;

use Testo\Attribute\Interceptable;
use Testo\Module\Interceptor\FallbackInterceptor;
use Testo\Sample\Internal\TestInlineInterceptor;

/**
 * Test that a method or function returns a specified result when called with given arguments.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE)]
#[FallbackInterceptor(TestInlineInterceptor::class)]
final class TestInline implements Interceptable
{
    public function __construct(
        public readonly array $arguments,
        /**
         * @var mixed|\Closure(mixed): mixed $result The expected result of the test method or function.
         */
        public readonly mixed $result = null,
    ) {}
}
