<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Test;

/**
 * A case without its own `#[Skip]`: the class-level attribute comes from {@see SkipMarkerTrait}.
 */
#[Test]
final class SkipTraitStub
{
    use SkipMarkerTrait;

    public function parked(): void
    {
        throw new \LogicException('Must never run: the case is parked via the trait.');
    }
}
