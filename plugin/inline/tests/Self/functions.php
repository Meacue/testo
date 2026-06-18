<?php

declare(strict_types=1);

namespace Tests\Inline\Self;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Inline\Internal\InlineHandler;
use Testo\Inline\TestInline;

#[Covers(InlineHandler::class)]
#[Covers(TestInline::class)]
#[TestInline(arguments: [1, 1], result: 2)]
#[TestInline(arguments: [40, 2], result: 42)]
function sum(int $a, int $b): int
{
    return $a + $b;
}

#[Covers(InlineHandler::class)]
#[Covers(TestInline::class)]
#[TestInline(arguments: [])]
function voidFunction(): void {}

/**
 * Named arguments resolve by name regardless of declaration order.
 */
#[Covers(InlineHandler::class)]
#[Covers(TestInline::class)]
#[TestInline(arguments: ['b' => 'foo', 'a' => 'bar'], result: 'bar-foo')]
#[TestInline(arguments: ['foo', 'bar'], result: 'foo-bar')]
function concat(string $a, string $b): string
{
    Assert::true(true);
    return $a . '-' . $b;
}
