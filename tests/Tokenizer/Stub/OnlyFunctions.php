<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

function freeFunctionOne(): void {}

function freeFunctionTwo(int $n): int
{
    return $n * 2;
}
