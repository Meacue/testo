<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

// Top-level closures whose parameter types and bodies reference (unqualified) classes.
// The old name scanner overshot the parameter list and registered `Covers` as a free function.
$factory = static function (Covers $covers): void {
    Covers::register();
    new Whatever();
};

// By-ref closure with no parameters at all — `function &()`.
$byRef = function &() {
    static $state;
    return $state;
};

// A genuine free function (with a class-typed parameter) must still be detected by its real name.
function realFreeFunction(Covers $covers): void {}
