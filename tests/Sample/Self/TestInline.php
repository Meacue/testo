<?php

declare(strict_types=1);

namespace Tests\Sample\Self;

use Testo\Assert;
use Testo\Attribute\ExpectException;
use Testo\Attribute\Test;
use Testo\Expect;

/**
 * @see Expect::leaks()
 */
final class TestInline
{
    #[\Testo\Sample\TestInline(arguments: [40, 2], result: 42)]
    public function sum(int $a, int $b): int
    {
        return $a + $b;
    }
}
