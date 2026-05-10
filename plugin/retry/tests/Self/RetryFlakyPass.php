<?php

declare(strict_types=1);

namespace Tests\Retry\Self;

use Testo\Assert;
use Testo\Retry;
use Testo\Test;

/**
 * Self-test for Retry. The test fails on the first attempt and passes on the second.
 * If the run completes successfully (Flaky status), Retry actually re-executed the test.
 */
#[Test]
final class RetryFlakyPass
{
    #[Retry(maxAttempts: 2)]
    public function flaky(): void
    {
        static $attempt = 0;
        ++$attempt;
        Assert::same(2, $attempt);
    }
}
