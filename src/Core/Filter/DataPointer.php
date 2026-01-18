<?php

declare(strict_types=1);

namespace Testo\Core\Filter;

use Testo\Application\Middleware\FilterInterceptor;

/**
 * Data provider index pointer for test filtering.
 *
 * Used in filter format: `testName:providerIndex:datasetIndex`
 * Allows running specific data provider datasets instead of all variants.
 *
 * Indices are 0-based and independent of dataset labels/names.
 * Dataset index is optional - null means run all datasets from provider.
 *
 * Examples:
 * - DataPointer(0, 1): Run provider #0, dataset #1 only
 * - DataPointer(2, null): Run provider #2, all datasets
 *
 * @see FilterInterceptor For usage in test filtering
 */
final class DataPointer
{
    /**
     * @param int<0, max> $provider The provider index.
     * @param int<0, max>|null $dataset The dataset index, or null to run all datasets from provider.
     */
    public function __construct(
        public readonly int $provider,
        public readonly ?int $dataset,
    ) {}
}
