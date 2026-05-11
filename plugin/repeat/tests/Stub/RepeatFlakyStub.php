<?php

declare(strict_types=1);

namespace Tests\Repeat\Stub;

use Testo\Assert;
use Testo\Repeat;
use Testo\Test;

/**
 * Stub with repeated tests that tolerate a configured number of failures.
 */
final class RepeatFlakyStub
{
    private int $toleratedCounter = 0;
    private int $exceededCounter = 0;
    private int $noFlakyCounter = 0;

    /**
     * Fails on the 2nd iteration out of 4; 1 failure is within `maxFailures: 2`.
     */
    #[Test]
    #[Repeat(times: 4, maxFailures: 2)]
    public function failureWithinThreshold(): void
    {
        ++$this->toleratedCounter;
        Assert::notSame($this->toleratedCounter, 2);
    }

    /**
     * Fails on iterations 1, 2 and 3 — the 3rd failure exceeds `maxFailures: 2`.
     */
    #[Test]
    #[Repeat(times: 5, maxFailures: 2)]
    public function failureExceedsThreshold(): void
    {
        ++$this->exceededCounter;
        Assert::true($this->exceededCounter > 3);
    }

    /**
     * Failure within threshold, but `markFlaky: false` keeps the status passing.
     */
    #[Test]
    #[Repeat(times: 4, maxFailures: 2, markFlaky: false)]
    public function failureWithinThresholdNoFlakyMark(): void
    {
        ++$this->noFlakyCounter;
        Assert::notSame($this->noFlakyCounter, 2);
    }
}
