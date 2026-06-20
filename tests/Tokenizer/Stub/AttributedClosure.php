<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

// Attribute placed on an anonymous function on the same line. Both the attribute argument and the
// typed parameter reference classes (T_STRING) that must NOT be mistaken for the function name.
$handler = #[Covers(Something::class)] static function (Covers $covers): void {
    Covers::run();
};

// A named function carrying an attribute on the same line must still be registered by name.
#[Covers(Something::class)] function namedWithAttribute(Covers $covers): void {}
