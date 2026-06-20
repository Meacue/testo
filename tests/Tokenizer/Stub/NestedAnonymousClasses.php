<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

// A parent anonymous class with sibling and deeply nested anonymous classes. The body ranges must
// nest correctly so every anonymous method is skipped and the declarations that follow the parent
// (a free function and a real class) are still detected — i.e. the parent range must not run long.
$root = new class {
    public function parentMethod(Covers $a): void {}

    public function makeFirst(): object
    {
        return new class {
            public function firstChild(Covers $b): void {}
        };
    }

    public function makeSecond(): object
    {
        return new class {
            public function secondChild(Covers $c): void
            {
                $deep = new class {
                    public function grandChild(Covers $d): void {}
                };
            }
        };
    }
};

function afterParentAnon(Covers $e): void {}

final class RealAfterAnon
{
    public function realMethod(Covers $f): void {}
}
