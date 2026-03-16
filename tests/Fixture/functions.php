<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Testo\Retry;
use Testo\Test;

#[Test]
#[Retry(maxAttempts: 3, markFlaky: false)]
function withRetryPolicy(): int
{
    static $runs = 0;
    ++$runs < 3 and throw new \RuntimeException('Failed attempt ' . $runs);
    return $runs;
}
