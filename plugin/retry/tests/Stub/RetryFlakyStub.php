<?php

declare(strict_types=1);

namespace Tests\Retry\Stub;

use Testo\Assert;
use Testo\Retry;
use Testo\Test;

/**
 * Stub with tests that fail on early attempts and pass later — should be marked Flaky.
 */
final class RetryFlakyStub
{
    private int $secondAttemptCounter = 0;
    private int $thirdAttemptCounter = 0;

    #[Test]
    #[Retry]
    public function passesOnSecondAttempt(): void
    {
        ++$this->secondAttemptCounter;
        Assert::same($this->secondAttemptCounter, 2);
    }

    #[Test]
    #[Retry]
    public function passesOnThirdAttempt(): void
    {
        ++$this->thirdAttemptCounter;
        Assert::same($this->thirdAttemptCounter, 3);
    }
}
