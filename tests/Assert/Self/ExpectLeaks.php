<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Attribute\ExpectException;
use Testo\Attribute\Test;
use Testo\Expect;

/**
 * @see Expect::leaks()
 */
final class ExpectLeaks
{
    #[Test]
    public function cachedStatically(): void
    {
        static $leak = null;
        $leak = [
            new \stdClass(),
            new \DateTimeImmutable(),
        ];
        Expect::leaks(...$leak)->message('foo bar');
    }

    #[Test]
    #[ExpectException(Assert\State\Expectation\ExpectLeaksFailure::class)]
    public function leaks(): void
    {
        $leak = new \stdClass();
        Expect::leaks($leak)->message('foo bar');
    }
}
