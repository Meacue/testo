<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::equals()
 */
#[Test]
#[Covers(Assert::class, 'equals')]
final class AssertEquals
{
    public function numbers(): never
    {
        Assert::equals(1, 1);
        Assert::equals(1.0, 1);
        Assert::equals(1, 1.0);
        Assert::equals(2, "2");

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::equals(1, 2, 'my wonderful message');
    }

    public function arrays(): void
    {
        # Same
        Assert::equals([1, 2], [1, 2]);
        Assert::equals(
            ['a' => 1, 'b' => 2],
            ['a' => 1, 'b' => 2],
        );
        # Different order
        Assert::equals(
            ['a' => 1, 'b' => 2],
            ['b' => 2, 'a' => 1],
        );
    }

    public function objects(): void
    {
        Assert::equals(
            (object) ['b' => 2, 'a' => 1],
            (object) ['a' => 1, 'b' => 2],
        );
    }
}
