<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Retry;
use Testo\Test;
use Testo\Test\Skip;

/**
 * `#[Skip]` composed with `#[Retry]`: the retry policy is resolved in the per-test pipeline, which
 * a skipped test never enters, so the body must not run at all. The counter tells a single stray
 * run apart from a full retry cycle. The enabled neighbor carries the same attribute with the flaky
 * mark off and fails its first attempt on purpose: two attempts per run prove the policy is live
 * in this suite.
 */
#[Test]
final class SkipWithRetryStub
{
    public static int $attempts = 0;
    public static int $enabledAttempts = 0;

    #[Skip('parked, retry must not engage')]
    #[Retry(maxAttempts: 3)]
    public function parked(): void
    {
        ++self::$attempts;
        throw new \LogicException('Must never run: the test is parked.');
    }

    #[Retry(maxAttempts: 3, markFlaky: false)]
    public function enabled(): void
    {
        # Control neighbor: the counter is even at the start of every run, so the first attempt
        # makes it odd and fails, the second makes it even and passes — two attempts per run.
        ++self::$enabledAttempts % 2 === 0 or throw new \RuntimeException('First attempt fails by design.');
    }
}
