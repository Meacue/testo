<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::notEquals()
 */
#[Test]
#[Covers(Assert::class, 'notEquals')]
final class AssertNotEquals
{
    public function numbers(): never
    {
        Assert::notEquals(2, 1);

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::notEquals(1, 1, 'my wonderful message');
    }

    public function arrays(): void
    {
        Assert::notEquals([1, 2], [2, 1]);
    }

    public function objects(): void
    {
        Assert::notEquals(
            (object) ['a' => 2],
            (object) ['a' => 1],
        );
    }
}
