<?php

declare(strict_types=1);

namespace Tests\Repeat\Unit;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Data\DataSet;
use Testo\Expect;
use Testo\Repeat;
use Testo\Test;

#[Test]
#[Covers(Repeat::class)]
final class RepeatTest
{
    public function defaultTimesIsTwo(): void
    {
        $repeat = new Repeat();

        Assert::same($repeat->times, 2);
    }

    public function customTimes(): void
    {
        $repeat = new Repeat(times: 5);

        Assert::same($repeat->times, 5);
    }

    public function timesOneIsValid(): void
    {
        $repeat = new Repeat(times: 1);

        Assert::same($repeat->times, 1);
    }

    /**
     * @param int<min, 0> $times Non-positive run counts are rejected by the constructor.
     */
    #[DataSet([0], 'zero')]
    #[DataSet([-1], 'negative')]
    #[DataSet([\PHP_INT_MIN], 'int min')]
    public function nonPositiveTimesFails(int $times): never
    {
        Expect::exception(\InvalidArgumentException::class)
            ->withMessage('Times must be greater than 0.');

        new Repeat(times: $times);
    }

    public function defaultMaxFailuresIsZero(): void
    {
        $repeat = new Repeat();

        Assert::same($repeat->maxFailures, 0);
    }

    public function customMaxFailures(): void
    {
        $repeat = new Repeat(maxFailures: 3);

        Assert::same($repeat->maxFailures, 3);
    }

    public function defaultMarkFlakyIsTrue(): void
    {
        $repeat = new Repeat();

        Assert::true($repeat->markFlaky);
    }

    public function customMarkFlaky(): void
    {
        $repeat = new Repeat(markFlaky: false);

        Assert::false($repeat->markFlaky);
    }

    /**
     * @param int<min, -1> $maxFailures Negative tolerances are rejected by the constructor.
     */
    #[DataSet([-1], 'negative')]
    #[DataSet([\PHP_INT_MIN], 'int min')]
    public function negativeMaxFailuresFails(int $maxFailures): never
    {
        Expect::exception(\InvalidArgumentException::class)
            ->withMessage('Max failures must be greater than or equal to 0.');

        new Repeat(maxFailures: $maxFailures);
    }
}
