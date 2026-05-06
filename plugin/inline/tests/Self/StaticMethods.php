<?php

declare(strict_types=1);

namespace Tests\Inline\Self;

use Testo\Assert;
use Testo\Inline\TestInline;

final class StaticMethods
{
    #[TestInline(arguments: [1, 1], result: 2)]
    #[TestInline(arguments: [40, 2], result: 42)]
    public static function publicSum(int $a, int $b): int
    {
        return $a + $b;
    }

    #[TestInline(arguments: [])]
    public static function publicVoid(): void
    {
        Assert::true(true);
    }

    #[TestInline(arguments: ['foo', 'bar'], result: 'foo-bar')]
    #[TestInline(arguments: ['b' => 'foo', 'a' => 'bar'], result: 'bar-foo')]
    protected static function protectedConcat(string $a, string $b): string
    {
        return $a . '-' . $b;
    }

    #[TestInline(arguments: [3, 4], result: 12)]
    #[TestInline(arguments: [-2, 5], result: -10)]
    private static function privateMul(int $a, int $b): int
    {
        return $a * $b;
    }

    /**
     * Verifies a static method can call another static member of the same class.
     */
    #[TestInline(arguments: [5], result: 105)]
    public static function dependsOnStatic(int $delta): int
    {
        return self::base() + $delta;
    }

    private static function base(): int
    {
        return 100;
    }
}
