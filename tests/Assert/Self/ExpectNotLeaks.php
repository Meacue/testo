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
final class ExpectNotLeaks
{
    #[Test]
    #[ExpectException(Assert\State\Expectation\ExpectNotLeaksFailure::class)]
    public function cachedStatically(): void
    {
        static $leak = null;
        $leak = [
            new \stdClass(),
            new \DateTimeImmutable(),
        ];
        Expect::notLeaks(...$leak)->message('foo bar');
    }

    #[Test]
    public function notLeaks(): void
    {
        $leak = new \stdClass();
        Expect::notLeaks($leak)->message('foo bar');
    }
}
