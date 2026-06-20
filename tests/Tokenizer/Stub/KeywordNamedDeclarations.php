<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

// Method names may be semi-reserved keywords. Their name token is not T_STRING, so the locator
// must read the name by position (the token before "(") rather than by token type.
final class KeywordNames
{
    public function list(): array
    {
        return [];
    }

    public function print(): void {}

    public function unset(): void {}
}
