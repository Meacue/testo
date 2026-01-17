<?php

declare(strict_types=1);

namespace Tests\Sample\Self;

use Testo\Attribute\Test;
use Testo\Sample\DataProvider;
use Testo\Sample\DataSet;

final class DataProviders
{
    public static function numbersProvider(): array
    {
        return [
            [1, 2],
            [3, 4],
            [5, 6],
        ];
    }

    #[Test]
    #[DataProvider('numbersProvider')]
    #[DataProvider('bigNumbersProvider')]
    #[DataSet([7, 8], 'seven-eight')]
    #[DataSet(['b' => 7, 'a' => 8])]
    public function sum(int $a, int $b): int
    {
        return $a + $b;
    }

    private static function bigNumbersProvider(): array
    {
        return [
            [100, 200],
            [300, 400],
            [500, 600],
        ];
    }
}
