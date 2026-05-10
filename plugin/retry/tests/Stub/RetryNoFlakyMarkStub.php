<?php

declare(strict_types=1);

namespace Tests\Retry\Stub;

use Testo\Assert;
use Testo\Retry;
use Testo\Test;

/**
 * Stub that passes on retry but disables the flaky mark — final status should remain Passed.
 */
final class RetryNoFlakyMarkStub
{
    private int $counter = 0;

    #[Test]
    #[Retry(markFlaky: false)]
    public function passesOnSecondAttempt(): void
    {
        ++$this->counter;
        Assert::same($this->counter, 2);
    }
}
