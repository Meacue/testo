<?php

declare(strict_types=1);

namespace Tests\Fixture;

use Testo\Application\Attribute\Test;

final class TestAttributes
{
    #[Test]
    public function withTestAttribute(): void {}

    public function testWithTestPrefix(): void {}
}
