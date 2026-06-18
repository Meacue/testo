<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\Internal\Assertion\AssertIterable as AssertIterableImpl;
use Testo\Assert\Internal\Assertion\Traits\IterableTrait;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Data\DataSet;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::count()
 */
#[Test]
#[Covers(Assert::class, 'count')]
#[Covers(AssertIterableImpl::class)]
#[Covers(IterableTrait::class)]
final class AssertCount
{
    public function countable(): never
    {
        Assert::count(new \ArrayObject([1, 2, 3]), 3);
        Assert::count(new \ArrayObject(), 0);

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::count(new \ArrayObject([1, 2, 3]), 5, 'my wonderful message');
    }

    public function array(): void
    {
        Assert::count([1, 2, 3], 3);
        Assert::count([], 0);
    }

    public function generator(): void
    {
        Assert::count($this->generateItems(3), 3);
        Assert::count($this->generateItems(0), 0);
    }

    /**
     * @param iterable<mixed> $actual
     */
    #[DataSet([[1, 2, 3], 1], 'array wrong count')]
    #[DataSet([[1, 2, 3], 5], 'array too few')]
    public function arrayFails(iterable $actual, int $expected): never
    {
        Expect::exception(AssertionException::class);
        Assert::count($actual, $expected);
    }

    public function generatorFails(): never
    {
        Expect::exception(AssertionException::class);
        Assert::count($this->generateItems(3), 5);
    }

    /**
     * @return \Generator<int, int>
     */
    private function generateItems(int $count): \Generator
    {
        for ($i = 0; $i < $count; $i++) {
            yield $i;
        }
    }
}
