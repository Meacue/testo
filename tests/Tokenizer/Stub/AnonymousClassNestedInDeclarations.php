<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

// Anonymous class returned from a free function — its method must not become a free function.
function makesAnon(): object
{
    return new class {
        public function insideFunction(Covers $covers): void {}
    };
}

final class HostsAnon
{
    // Anonymous class returned from a method — its method must not be attributed to HostsAnon.
    public function make(): object
    {
        return new class {
            public function insideMethod(Covers $covers): void {}
        };
    }

    public function plainMethod(): void {}
}
