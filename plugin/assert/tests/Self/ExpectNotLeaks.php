<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert\ExpectException;
use Testo\Assert\Internal\Expectation\NotLeaks;
use Testo\Assert\State\Expectation\ExpectNotLeaksFailure;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

/**
 * @see Expect::notLeaks()
 */
#[Test]
#[Covers(Expect::class, 'notLeaks')]
#[Covers(NotLeaks::class)]
#[Covers(ExpectNotLeaksFailure::class)]
final class ExpectNotLeaks
{
    #[ExpectException(ExpectNotLeaksFailure::class)]
    public function cachedStatically(): void
    {
        static $leak = null;
        $leak = [
            new \stdClass(),
            new \DateTimeImmutable(),
        ];
        Expect::notLeaks(...$leak)->message('foo bar');
    }

    public function notLeaks(): void
    {
        $leak = new \stdClass();
        Expect::notLeaks($leak)->message('foo bar');
    }
}
