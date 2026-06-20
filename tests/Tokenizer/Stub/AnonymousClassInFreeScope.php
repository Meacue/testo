<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

// Methods of an anonymous class declared at file scope must not leak into the free-function list.
$service = new class {
    public function handle(Covers $covers): void {}
};

function freeAlongsideAnon(Covers $covers): void {}
