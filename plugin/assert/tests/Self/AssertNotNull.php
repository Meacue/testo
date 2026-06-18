<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::notNull()
 */
#[Test]
#[Covers(Assert::class, 'notNull')]
final class AssertNotNull
{
    public function checkNonNullValues(): void
    {
        Assert::notNull(0);
        Assert::notNull('');
        Assert::notNull(false);
        Assert::notNull([]);
        Assert::notNull(0.0);
        Assert::notNull(new \stdClass());
    }

    public function checkNullFails(): never
    {
        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::notNull(null, 'my wonderful message');
    }
}
