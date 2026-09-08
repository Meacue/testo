<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Repeat;
use Testo\Test;
use Testo\Test\Skip;

/**
 * `#[Skip]` on a test that also carries `#[Repeat]`: the repeat is resolved in the per-test
 * pipeline, which a skipped test never enters, so the body must not run at all. The latch is
 * never reset — {@see \Tests\Test\Feature\SkipFeatureTest::repeatDoesNotEngageForParkedTest()}
 * asserts it absolutely, not as a delta. The enabled neighbor carries the same attribute and
 * counts its runs: three per run prove the repeat is live in this suite.
 */
#[Test]
final class SkipWithRepeatStub
{
    public static bool $bodyRan = false;
    public static int $enabledRuns = 0;

    #[Skip('parked, repeat must not engage')]
    #[Repeat(times: 3)]
    public function parked(): void
    {
        self::$bodyRan = true;
        throw new \LogicException('Must never run: the test is parked.');
    }

    #[Repeat(times: 3)]
    public function enabled(): void
    {
        # Control neighbor: repeated three times per run.
        ++self::$enabledRuns;
    }
}
