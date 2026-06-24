<?php

declare(strict_types=1);

namespace Tests\Sandbox\Self\Inherited;

use Testo\Assert;
use Testo\Test;

/**
 * Concrete subclass: inherits two #[Test] methods from {@see AbstractInheritedTest} and adds its
 * own. All three must be discovered and attributed to this class, and selectable via `--filter`
 * both by class (`ConcreteInheritedTest`) and by method (`ConcreteInheritedTest::inheritedFromBase`).
 */
#[Test]
final class ConcreteInheritedTest extends AbstractInheritedTest
{
    public function ownTest(): void
    {
        Assert::true(true);
    }
}
