<?php

declare(strict_types=1);

namespace Tests\Test\Unit\Fixture;

use Testo\Test\Skip;

/**
 * Used by {@see \Tests\Test\Unit\Internal\SkipInterceptorTest}: a class-level `#[Skip]`
 * parks every test; a method-level reason wins over the class-level one.
 */
#[Skip('entire case is parked')]
final class SkipClassLevelFixture
{
    public function first(): void
    {
        throw new \LogicException('Must never run: the test is parked.');
    }

    #[Skip('method beats class')]
    public function second(): void
    {
        throw new \LogicException('Must never run: the test is parked.');
    }
}
