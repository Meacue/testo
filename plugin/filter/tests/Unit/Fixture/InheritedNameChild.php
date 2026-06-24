<?php

declare(strict_types=1);

namespace Tests\Filter\Unit\Fixture;

use Testo\Test;

/**
 * Concrete subclass: inherits {@see InheritedNameBase::inheritedName()} and
 * {@see InheritedNameBase::alsoInheritedName()} (no override) and declares its own {@see childName()}.
 *
 * This file's only method token is `childName` — the inherited ones are tokenized in the parent's
 * file under the parent's FQN. Filtering by an inherited method name therefore exercises the gap in
 * the token-based Stage 1 pre-filter.
 */
final class InheritedNameChild extends InheritedNameBase
{
    #[Test]
    public function childName(): void {}
}
