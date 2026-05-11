<?php

declare(strict_types=1);

namespace Tests\Repeat\Unit;

use Testo\Assert;
use Testo\Repeat;
use Testo\Test;

#[Test]
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

    public function zeroTimesThrowsException(): void
    {
        self::assertThrowsInvalidArgument(0);
    }

    public function negativeTimesThrowsException(): void
    {
        self::assertThrowsInvalidArgument(-1);
    }

    public function intMinTimesThrowsException(): void
    {
        self::assertThrowsInvalidArgument(\PHP_INT_MIN);
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

    public function negativeMaxFailuresThrowsException(): void
    {
        self::assertThrowsForMaxFailures(-1);
    }

    public function intMinMaxFailuresThrowsException(): void
    {
        self::assertThrowsForMaxFailures(\PHP_INT_MIN);
    }

    private static function assertThrowsInvalidArgument(int $times): void
    {
        $thrown = null;
        try {
            new Repeat(times: $times);
        } catch (\InvalidArgumentException $e) {
            $thrown = $e;
        }

        Assert::instanceOf($thrown, \InvalidArgumentException::class);
        Assert::same($thrown->getMessage(), 'Times must be greater than 0.');
    }

    private static function assertThrowsForMaxFailures(int $maxFailures): void
    {
        $thrown = null;
        try {
            new Repeat(maxFailures: $maxFailures);
        } catch (\InvalidArgumentException $e) {
            $thrown = $e;
        }

        Assert::instanceOf($thrown, \InvalidArgumentException::class);
        Assert::same($thrown->getMessage(), 'Max failures must be greater than or equal to 0.');
    }
}
