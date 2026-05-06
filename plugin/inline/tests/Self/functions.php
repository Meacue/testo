<?php

declare(strict_types=1);

namespace Tests\Inline\Self;

use Testo\Assert;
use Testo\Inline\TestInline;

#[TestInline(arguments: [1, 1], result: 2)]
#[TestInline(arguments: [40, 2], result: 42)]
function sum(int $a, int $b): int
{
    return $a + $b;
}

#[TestInline(arguments: [])]
function voidFunction(): void {}

#[TestInline(arguments: ['b' => 'foo', 'a' => 'bar'], result: 'bar-foo')]
#[TestInline(arguments: ['foo', 'bar'], result: 'foo-bar')]
function concat(string $a, string $b): string
{
    Assert::true(true);
    return $a . '-' . $b;
}
