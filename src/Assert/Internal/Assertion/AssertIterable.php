<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\IterableType;
use Testo\Assert\Internal\Assertion\Traits\IterableTrait;
use Testo\Assert\State\AssertTypeFailure;
use Testo\Assert\StaticState;
use Testo\Assert\Support;

/**
 * Assertion utilities for iterables.
 *
 * @internal
 */
class AssertIterable implements IterableType
{
    use IterableTrait;

    public function __construct(
        private readonly iterable $value,
    ) {}

    /**
     * Validate that the given value is an iterable and return an AssertIterable instance.
     *
     * @param mixed $value The value to be asserted as an iterable.
     * @return self An instance of AssertIterable.
     * @throws AssertTypeFailure when the value is not an iterable.
     */
    public static function validateAndCreate(mixed $value): self
    {
        \is_iterable($value) or StaticState::fail(AssertTypeFailure::create('iterable', $value));

        StaticState::log('Assert iterable: ' . Support::stringify($value));
        return new self($value);
    }
}
