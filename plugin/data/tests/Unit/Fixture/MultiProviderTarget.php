<?php

declare(strict_types=1);

namespace Tests\Data\Unit\Fixture;

use Testo\Data\DataProvider;
use Testo\Data\DataSet;

/**
 * Fixture with several data provider attributes on a single test method.
 *
 * Used to verify that {@see \Testo\Data\Internal\DataProviderInterceptor} accumulates
 * results across all providers instead of keeping only the last one.
 */
final class MultiProviderTarget
{
    public static function firstProvider(): array
    {
        return [[1], [2]];
    }

    public static function secondProvider(): array
    {
        return [[3], [4], [5]];
    }

    #[DataProvider('firstProvider')]
    #[DataProvider('secondProvider')]
    #[DataSet([6])]
    public function target(int $value): void {}
}
