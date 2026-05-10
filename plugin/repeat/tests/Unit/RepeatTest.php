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
}
