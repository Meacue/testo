<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

// `new #[Attr] class` — an attribute sits between `new` and `class`, yet it is still anonymous.
// Its method must not be registered as a declaration.
$listener = new #[Covers(Something::class)] class {
    public function on(Covers $covers): void {}
};
