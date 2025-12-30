<?php

declare(strict_types=1);

namespace Tests\Sample\Self;

use Testo\Attribute\Test;
use Testo\Expect;

/**
 * @see Expect::leaks()
 */
final class TestInline
{
    #[\Testo\Sample\TestInline(arguments: [1, 1], result: 2)]
    #[\Testo\Sample\TestInline(arguments: [40, 2], result: 42)]
    public function sum(int $a, int $b): int
    {
        return $a + $b;
    }
}
