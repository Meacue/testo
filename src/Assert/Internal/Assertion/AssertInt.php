<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\IntType;
use Testo\Assert\Internal\Assertion\Traits\NumericTrait;
use Testo\Assert\State\AssertTypeFailure;
use Testo\Assert\State\AssertionComposite;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Assertion utilities for integer data type.
 *
 * @internal
 */
class AssertInt implements IntType
{
    use NumericTrait;

    public function __construct(
        private readonly int $value,
        private readonly AssertionComposite $parent,
    ) {}

    /**
     * Validate that the given value is an integer and return an AssertInt instance.
     *
     * @param mixed $value The value to be asserted as integer.
     * @return self An instance of AssertInt.
     * @throws AssertTypeFailure when the value is not an integer.
     */
    public static function validateAndCreate(mixed $value): self
    {
        \is_int($value) or StaticState::typeFail('int', $value);

        $parent = StaticState::typeSuccess('int', $value);
        return new self($value, $parent);
    }
}
