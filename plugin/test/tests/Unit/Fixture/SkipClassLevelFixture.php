<?php

declare(strict_types=1);

namespace Tests\Test\Unit\Fixture;

use Testo\Test\Skip;

/**
 * Fixture with a class-level `#[Skip]` and method-level overrides.
 *
 * Used by {@see \Tests\Test\Unit\Internal\SkipInterceptorTest}: a class-level `#[Skip]`
 * parks every test; a method-level `#[Skip]` wins over the class-level one, reason included —
 * also when its own reason is empty.
 */
#[Skip('entire case is parked')]
final class SkipClassLevelFixture
{
    public function first(): void {}

    #[Skip('method beats class')]
    public function second(): void {}

    #[Skip]
    public function third(): void {}
}
