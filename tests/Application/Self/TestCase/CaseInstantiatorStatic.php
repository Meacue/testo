<?php

declare(strict_types=1);

namespace Tests\Application\Self\TestCase;

use Testo\Assert;
use Testo\Test;

/**
 * When a test case exposes only static test methods, Testo must not instantiate the class.
 */
#[Test]
final class CaseInstantiatorStatic
{
    public function __construct()
    {
        throw new \LogicException('Constructor must not be called.');
    }

    public static function firstStaticMethod(): void
    {
        Assert::true(true);
    }

    public static function secondStaticMethod(): void
    {
        Assert::false(false);
    }
}
