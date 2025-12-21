<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\ObjectType;
use Testo\Assert\State\AssertException;
use Testo\Assert\State\Assertion\AssertionComposite;
use Testo\Assert\StaticState;

/**
 * Assertion utilities for iterables.
 *
 * @internal
 */
final class AssertObject implements ObjectType
{
    public function __construct(
        private readonly object $value,
        private readonly AssertionComposite $parent,
    ) {}

    /**
     * @template ValueType
     *
     * @param ValueType $value The value to be asserted as float.
     * @throws AssertException
     *
     * @psalm-assert object $value
     * @phpstan-assert object $value
     */
    public static function validateAndCreate(mixed $value): self
    {
        \is_object($value) or StaticState::typeFail('object', $value);

        $parent = StaticState::typeSuccess('object', $value);
        return new self($value, $parent);
    }

    #[\Override]
    public function instanceOf(string $expected, string $message = ''): static
    {
        $str = "is instance of `{$expected}`";
        $this->value instanceof $expected
            ? $this->parent->success($str, $message)
            : throw $this->parent->fail($str, 'got `' . $this->value::class . '` instead', $message);
        return $this;
    }

    #[\Override]
    public function hasProperty(string $propertyName, string $message = ''): static
    {
        throw new \LogicException('Not implemented yet');
    }
}
