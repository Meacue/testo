<?php

declare(strict_types=1);

namespace Tests\Application\Self\TestCase;

use Testo\Assert;
use Testo\Test;

/**
 * The same test case instance must be reused across non-static test methods of the same class.
 */
#[Test]
final class CaseInstantiatorCache
{
    private static bool $initialized = false;

    public function __construct()
    {
        self::$initialized and throw new \RuntimeException('Test case instance reused.');

        self::$initialized = true;
    }

    public function firstInstanceMethod(): void
    {
        Assert::true(true);
    }

    public function secondInstanceMethod(): void
    {
        Assert::false(false);
    }
}
