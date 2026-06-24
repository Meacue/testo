<?php

declare(strict_types=1);

namespace Tests\Filter\Unit\Fixture;

use Testo\Test;

/**
 * Flat (non-inheriting) test case whose only `extends` sits on an anonymous class buried in a method
 * body. An anonymous class is never a discoverable test case, so the Stage 1 pre-filter must not let
 * its `extends` make the file look like it declares a subclass — otherwise a fragment filter that
 * matches nothing declared here would still keep the file.
 */
#[Test]
final class AnonymousExtendsFixture
{
    public function ownTest(): void
    {
        $helper = new class extends \RuntimeException {};
    }
}
