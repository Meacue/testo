<?php

declare(strict_types=1);

namespace Tests\Retry\Stub;

use Testo\Assert;
use Testo\Retry;
use Testo\Test;

/**
 * Stub with a test that passes on the first attempt — no retry should happen.
 */
final class RetryPassingStub
{
    #[Test]
    #[Retry]
    public function passesFirstTime(): void
    {
        Assert::true(true);
    }
}
