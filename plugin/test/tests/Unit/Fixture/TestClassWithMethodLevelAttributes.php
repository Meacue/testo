<?php

declare(strict_types=1);

namespace Tests\Test\Unit\Fixture;

use Testo\Test;

final class TestClassWithMethodLevelAttributes
{
    #[Test]
    public function publicTest(): void {}

    #[Test]
    protected function protectedTest(): void {}

    public function publicWithoutAttribute(): void {}

    #[Test]
    private function privateTest(): void {}

    #[Test]
    public function anotherPublicTest(): void {}
}
