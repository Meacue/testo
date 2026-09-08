<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Assert;
use Testo\Test;
use Testo\Test\Skip;

/**
 * Method-level `#[Skip]`, with and without a reason: only the marked tests of the case are
 * deactivated; the unmarked neighbor still runs. Both marked bodies throw, so a marked test that
 * reaches the pipeline anyway fails loudly instead of passing quietly.
 */
#[Test]
final class SkipMethodStub
{
    #[Skip('broken by the pricing rework, see ISSUE-123')]
    public function parked(): void
    {
        throw new \LogicException('Must never run: the test is parked.');
    }

    #[Skip]
    public function parkedNoReason(): void
    {
        throw new \LogicException('Must never run: the test is parked.');
    }

    public function enabled(): void
    {
        # Control neighbor: stays runnable next to the parked ones.
        Assert::true(true);
    }
}
