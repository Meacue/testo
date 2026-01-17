<?php

declare(strict_types=1);

namespace Testo\Sample;

use Testo\Attribute\Interceptable;
use Testo\Sample\Internal\DataProviderInterceptor;
use Testo\Module\Interceptor\FallbackInterceptor;
use Testo\Sample\Internal\DataProviderAttribute;

/**
 * Attribute to specify a data set for the test.
 *
 * Each data set is an array of arguments that will be passed to the test method.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::IS_REPEATABLE)]
#[FallbackInterceptor(DataProviderInterceptor::class)]
final class DataSet implements Interceptable, DataProviderAttribute
{
    /**
     * @param array $arguments The arguments for the data set.
     *        Can be an associative array to specify named arguments.
     * @param string|null $name An optional name for the data set.
     */
    public function __construct(
        public readonly array $arguments,
        public readonly ?string $name = null,
    ) {}
}
