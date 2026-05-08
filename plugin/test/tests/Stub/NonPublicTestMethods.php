<?php

declare(strict_types=1);

namespace Tests\Test\Stub;

use Testo\Assert;
use Testo\Test;

final class NonPublicTestMethods
{
    #[Test]
    public function publicTest(): void
    {
        Assert::true(true);
    }

    #[Test]
    protected function protectedTest(): void
    {
        Assert::true(true);
    }

    #[Test]
    private function privateTest(): void
    {
        Assert::true(true);
    }
}
