<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Test\Skip;

/**
 * Carries a class-level `#[Skip]` for {@see SkipTraitStub} to use — nothing else.
 */
#[Skip('inherited from the trait')]
trait SkipMarkerTrait {}
