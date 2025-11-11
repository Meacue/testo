<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\State\AssertException;
use Testo\Attribute\Test;
use Testo\Expect;

/**
 * Assertion examples.
 */
final class AssertInt
{
    #[Test]
    public function checkIntDataType(): void
    {
        // This assertion checks incoming data type
        Assert::int(42);

        Expect::exception(Assert\State\AssertTypeFailure::class);
        Assert::int('42');
    }

    #[Test]
    public function checkIntGreaterThan(): void
    {
        // actual is greater than min threshold
        Assert::int(42)->greaterThan(41);

        Expect::exception(Assert\State\AssertException::class);
        Assert::int(42)->greaterThan(43);
    }

    #[Test]
    public function checkIntGreaterThanOrEqual(): void
    {
        // actual is greater than or equal to min threshold
        Assert::int(42)->greaterThanOrEqual(41);
        Assert::int(42)->greaterThanOrEqual(42);

        Expect::exception(Assert\State\AssertException::class);
        Assert::int(42)->greaterThan(43);
    }
}
