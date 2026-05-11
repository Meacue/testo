<?php

declare(strict_types=1);

namespace Testo;

use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Repeat\Internal\RepeatInterceptor;

/**
 * Run a test multiple times.
 *
 * The {@see $times} parameter is the **total** number of runs, not extra repetitions on top
 * of an initial execution: `#[Repeat(times: 3)]` runs the test three times in total.
 *
 * When combined with {@see Retry}, Repeat runs inside Retry: each retry attempt
 * executes the full repeat cycle, and any single run failure triggers the retry logic.
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_CLASS)]
#[FallbackInterceptor(RepeatInterceptor::class)]
final readonly class Repeat implements Interceptable
{
    /**
     * @param int<1, max> $times Total number of test runs (not additional repetitions).
     *        `times: 1` runs the test once, `times: 3` runs it three times in total.
     *        Mirrors Kotlin's `repeat(times)` and JUnit's `@RepeatedTest(value)`.
     * @param int<0, max> $maxFailures Number of failed runs tolerated before the whole loop fails.
     *        With the default `0` any single failure stops the loop and fails the test.
     * @param bool $markFlaky Mark the test as flaky when at least one run failed
     *        but the failure count stayed within {@see $maxFailures}.
     */
    public function __construct(
        public int $times = 2,
        public int $maxFailures = 0,
        public bool $markFlaky = true,
    ) {
        $times > 0 or throw new \InvalidArgumentException('Times must be greater than 0.');
        $maxFailures >= 0 or throw new \InvalidArgumentException('Max failures must be greater than or equal to 0.');
    }
}
