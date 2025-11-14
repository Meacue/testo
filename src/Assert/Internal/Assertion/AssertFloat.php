<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\FloatType;
use Testo\Assert\Internal\Assertion\Traits\NumericTrait;
use Testo\Assert\State\AssertTypeFailure;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Assertion utilities for integer data type.
 *
 * @internal
 */
class AssertFloat implements FloatType
{
    use NumericTrait;

    private function __construct(
        private readonly float $value,
    ) {}

    /**
     * Validate that the given value is a float and return an AssertFloat instance.
     *
     * @param mixed $value The value to be asserted as float.
     * @return self An instance of AssertFloat.
     * @throws AssertTypeFailure when the value is not a float.
     */
    public static function create(mixed $value): self
    {
        \is_float($value) or StaticState::fail(AssertTypeFailure::create('float', $value));

        StaticState::log('Assert float: ' . Support::stringify($value));
        return new self($value);
    }
}
