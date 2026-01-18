<?php

declare(strict_types=1);

namespace Tests\Testo\Self;

use Testo\Application\Attribute\Test;
use Testo\Assert;

#[Test]
function simpleFunctionAssertions(): void
{
    Assert::same(1, 1);
    Assert::null(null);
    Assert::notSame(42, '42');
}
