<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Test\Skip;

/**
 * Abstract, so the locator never discovers it as its own case — the method-level `#[Skip]`
 * reaches the case only through {@see SkipOverridingMethodStub}, which overrides the method.
 */
abstract class SkipOverriddenMethodParentStub
{
    #[Skip('inherited from the overridden method')]
    public function parked(): void
    {
        throw new \LogicException('Must never run: the parent method is parked.');
    }
}
