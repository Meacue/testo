<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Retry;
use Testo\Test;
use Testo\Test\Skip;

/**
 * `#[Skip]` composed with `#[Retry]`: the retry policy is resolved in the per-test pipeline, which
 * a skipped test never enters, so the body must not run at all. The counter tells a single stray
 * run apart from a full retry cycle.
 */
#[Test]
final class SkipWithRetryStub
{
    public static int $attempts = 0;

    #[Skip('parked, retry must not engage')]
    #[Retry(maxAttempts: 3)]
    public function parked(): void
    {
        ++self::$attempts;
        throw new \LogicException('Must never run: the test is parked.');
    }
}
