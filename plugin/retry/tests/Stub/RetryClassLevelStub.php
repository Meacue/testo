<?php

declare(strict_types=1);

namespace Tests\Retry\Stub;

use Testo\Assert;
use Testo\Retry;
use Testo\Test;

/**
 * Stub with a class-level #[Retry] attribute applied to all tests inside.
 */
#[Retry(maxAttempts: 4)]
final class RetryClassLevelStub
{
    private int $counter = 0;

    #[Test]
    public function passesOnThirdAttempt(): void
    {
        ++$this->counter;
        Assert::same($this->counter, 3);
    }
}
