<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Attribute\Test;

/**
 * Assertion examples.
 */
final class AssertObject
{
    #[Test]
    public function instanceOf(): void
    {
        $obj = new \DateTimeImmutable();

        Assert::instanceOf(\DateTimeInterface::class, $obj);
        Assert::instanceOf(\DateTimeImmutable::class, $obj);
    }
}
