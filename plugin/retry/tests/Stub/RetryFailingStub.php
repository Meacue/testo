<?php

declare(strict_types=1);

namespace Tests\Retry\Stub;

use Testo\Assert;
use Testo\Retry;
use Testo\Test;

/**
 * Stub with tests that fail on every attempt — retries are exhausted and the result stays Failed.
 */
final class RetryFailingStub
{
    #[Test]
    #[Retry]
    public function alwaysFails(): void
    {
        Assert::fail('intentional failure');
    }

    #[Test]
    #[Retry(maxAttempts: 1)]
    public function singleAttemptNoRetry(): void
    {
        Assert::fail('intentional failure');
    }
}
