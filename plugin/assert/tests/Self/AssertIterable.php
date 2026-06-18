<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\Internal\Assertion\AssertIterable as AssertIterableImpl;
use Testo\Assert\Internal\Assertion\Traits\IterableTrait;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::iterable()
 */
#[Test]
#[Covers(Assert::class, 'iterable')]
#[Covers(AssertIterableImpl::class)]
#[Covers(IterableTrait::class)]
final class AssertIterable
{
    public function checkIterableType(): void
    {
        // This assertion checks incoming data type
        Assert::iterable(new \ArrayIterator([1, 2, 3]));
        Assert::iterable([]);
    }

    public function notEmpty(): never
    {
        Assert::iterable(new \ArrayIterator([1, 2, 3]))->notEmpty();
        Assert::iterable([1])->notEmpty();

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::iterable([])->notEmpty('my wonderful message');
    }

    public function contains(): never
    {
        Assert::iterable(new \ArrayIterator([1, 2, 3]))->contains(3);
        Assert::iterable([1, 2, 3])->contains(3);

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::iterable([1, 2, 3])->contains(4, 'my wonderful message');
    }

    public function notContains(): never
    {
        Assert::iterable(new \ArrayIterator([1, 2, 3]))->notContains(4);
        Assert::iterable([1, 2, 3])->notContains('3'); // strict comparison: string '3' is absent

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::iterable([1, 2, 3])->notContains(2, 'my wonderful message');
    }

    public function sameSizeAs(): never
    {
        Assert::iterable(new \ArrayIterator([1, 2, 3]))->sameSizeAs(new \ArrayIterator(['a', 'b', 'c']));
        Assert::iterable(new \ArrayIterator([1, 2, 3]))->sameSizeAs(['a', 'b', 'c']);

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::iterable([1, 2, 3])->sameSizeAs(['a', 'b'], 'my wonderful message');
    }

    public function every(): never
    {
        Assert::iterable(new \ArrayIterator([1, 2, 3]))->every(static fn($value) => \is_int($value));

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::iterable([1, 2, 'testo'])->every(static fn($value) => \is_int($value), 'my wonderful message');
    }

    public function allOf(): never
    {
        Assert::iterable(new \ArrayIterator([1, 2, 3]))->allOf('integer');
        Assert::iterable(['a', 'b', 'c'])->allOf('string');

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::iterable([true, false, 'true'])->allOf('bool', 'my wonderful message');
    }

    public function hasCount(): never
    {
        Assert::iterable(new \ArrayIterator([1, 2, 3]))->hasCount(3);
        Assert::iterable([1, 2, 3])->hasCount(3);

        Expect::exception(AssertionException::class);
        Assert::iterable([1, 2, 3])->hasCount(2);
    }
}
