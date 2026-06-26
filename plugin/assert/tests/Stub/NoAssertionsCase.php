<?php

declare(strict_types=1);

namespace Tests\Assert\Stub;

use Testo\Assert\ExpectNoAssertions;
use Testo\Test;

#[Test]
#[ExpectNoAssertions]
final class NoAssertionsCase
{
    public function first(): void
    {
        // No assertions here
    }

    public function second(): void
    {
        // No assertions here either
    }
}
