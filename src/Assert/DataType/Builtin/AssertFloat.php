<?php

declare(strict_types=1);

namespace Testo\Assert\DataType\Builtin;

use Testo\Assert\Traits\NumericTrait;

/**
 * Assertion utilities for integer data type.
 */
class AssertFloat
{
    use NumericTrait;

    public function __construct(
        public float $value,
    ) {}
}
