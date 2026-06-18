<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Data\DataSet;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::blank()
 */
#[Test]
#[Covers(Assert::class, 'blank')]
final class AssertBlank
{
    public function checkBlankData(): void
    {
        Assert::blank([]);
        Assert::blank("");
        Assert::blank(null);
        Assert::blank(new \ArrayIterator());
    }

    #[DataSet([0], 'integer zero')]
    #[DataSet(['0'], 'string zero')]
    #[DataSet([false], 'boolean false')]
    public function checkNotBlankFails(mixed $value): never
    {
        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::blank($value, 'my wonderful message');
    }
}
