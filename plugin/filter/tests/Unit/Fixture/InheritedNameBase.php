<?php

declare(strict_types=1);

namespace Tests\Filter\Unit\Fixture;

use Testo\Test;

/**
 * Abstract base declaring test methods that subclasses inherit WITHOUT overriding. Because the
 * methods live in this file, the subclass file carries no token for them — which is exactly what
 * the Stage 1 token pre-filter ({@see \Testo\Filter\Internal\FilterInterceptor::locateFile()}) sees.
 *
 * Kept in its own file (separate from {@see InheritedNameChild}) on purpose: co-locating them would
 * leak the method tokens into the child's file and hide the inheritance-aware pre-filter problem.
 */
abstract class InheritedNameBase
{
    #[Test]
    public function inheritedName(): void {}

    #[Test]
    public function alsoInheritedName(): void {}
}
