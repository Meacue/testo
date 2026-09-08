<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Test;
use Testo\Test\Skip;

/**
 * A class-level `#[Skip]`: both tests of the case are skipped with the class reason, proving the
 * attribute covers every test and not just the first one.
 */
#[Test]
#[Skip('the whole case is parked')]
final class SkipClassLevelStub
{
    public function firstParked(): void
    {
        throw new \LogicException('Must never run: the case is parked.');
    }

    public function secondParked(): void
    {
        throw new \LogicException('Must never run: the case is parked.');
    }
}
