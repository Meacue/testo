<?php

declare(strict_types=1);

namespace Tests\Retry\Unit;

use Testo\Assert;
use Testo\Retry;
use Testo\Test;

#[Test]
final class RetryTest
{
    public function defaultMaxAttemptsIsThree(): void
    {
        $retry = new Retry();

        Assert::same($retry->maxAttempts, 3);
    }

    public function defaultMarkFlakyIsTrue(): void
    {
        $retry = new Retry();

        Assert::true($retry->markFlaky);
    }

    public function customMaxAttempts(): void
    {
        $retry = new Retry(maxAttempts: 5);

        Assert::same($retry->maxAttempts, 5);
    }

    public function customMarkFlaky(): void
    {
        $retry = new Retry(markFlaky: false);

        Assert::false($retry->markFlaky);
    }

    public function maxAttemptsOneIsValid(): void
    {
        $retry = new Retry(maxAttempts: 1);

        Assert::same($retry->maxAttempts, 1);
    }
}
