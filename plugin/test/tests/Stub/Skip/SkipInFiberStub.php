<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Assert;
use Testo\Fiber\RunInFiber;
use Testo\Test;
use Testo\Test\Skip;

/**
 * A class-level `#[RunInFiber]` installs a fiber batch runner on the case; the skip
 * interceptor must wrap that runner, not replace it — the enabled test still runs on the
 * scheduler, the parked one is still reported as skipped.
 */
#[Test]
#[RunInFiber]
final class SkipInFiberStub
{
    #[Skip('parked inside a fiber-driven case')]
    public function parked(): void
    {
        throw new \LogicException('Must never run: the test is parked.');
    }

    public function enabled(): void
    {
        Assert::true(true);
    }
}
