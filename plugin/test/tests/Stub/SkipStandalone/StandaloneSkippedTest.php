<?php

declare(strict_types=1);

namespace Tests\Test\Stub\SkipStandalone;

use Testo\Test\Skip;

/**
 * The case of the standalone-fallback run: discovered by naming convention alone (no
 * `#[Test]` attribute), executed without `TestPlugin` — only the class-level `#[Skip]`
 * fallback skips these tests. Lives in its own directory so the standalone run's
 * `FinderConfig` can point at it alone and pick up nothing else.
 */
#[Skip('standalone case is skipped')]
final class StandaloneSkippedTest
{
    public function testFirstSkipped(): void
    {
        throw new \LogicException('Must never run: the case is skipped via the fallback.');
    }

    public function testSecondSkipped(): void
    {
        throw new \LogicException('Must never run: the case is skipped via the fallback.');
    }
}
