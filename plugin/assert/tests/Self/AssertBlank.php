<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Expect;
use Testo\Test;

/**
 * Assertion examples.
 */
final class AssertBlank
{
    #[Test]
    public function checkBlankData(): void
    {
        Assert::blank([]);
        Assert::blank("");
        Assert::blank(null);
        Assert::blank(new \ArrayIterator());
    }

    #[Test]
    public function checkZeroIsNotBlank(): void
    {
        Expect::exception(AssertionException::class);
        Assert::blank(0);
    }

    #[Test]
    public function checkZeroStringIsNotBlank(): void
    {
        Expect::exception(AssertionException::class);
        Assert::blank("0");
    }

    #[Test]
    public function checkFalseIsNotBlank(): void
    {
        Expect::exception(AssertionException::class);
        Assert::blank(false);
    }
}
