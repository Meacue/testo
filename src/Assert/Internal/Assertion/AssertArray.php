<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Assertion;

use Testo\Assert\Api\Builtin\ArrayType;
use Testo\Assert\Internal\Assertion\Traits\IterableTrait;
use Testo\Assert\State\AssertionException;
use Testo\Assert\State\AssertionComposite;
use Testo\Assert\StaticState;

/**
 * Assertion utilities for arrays.
 *
 * @internal
 */
class AssertArray implements ArrayType
{
    use IterableTrait;

    public function __construct(
        private readonly array $value,
        private readonly AssertionComposite $parent,
    ) {}

    /**
     * Validate that the given value is an array and return an AssertArray instance.
     *
     * @param mixed $value The value to be asserted as array.
     *
     * @throws AssertionException when the value is not an array.
     */
    public static function validateAndCreate(mixed $value): self
    {
        \is_array($value) or StaticState::typeFail('array', $value);

        $parent = StaticState::typeSuccess('array', $value);
        return new self($value, $parent);
    }

    #[\Override]
    public function hasKeys(int|string ...$keys): static
    {
        if ($keys === []) {
            return $this;
        }

        $failedKeys = [];
        foreach ($keys as $key) {
            if (\array_key_exists($key, $this->value)) {
                continue;
            }

            $failedKeys[] = $key;
        }

        $m = \count($keys) === 1 ? '' : 's';
        $str = "has key$m " . \implode(', ', $keys);
        if ($failedKeys === []) {
            $this->parent->success($str);
            return $this;
        }

        $m = \count($failedKeys) === 1 ? '' : 's';
        throw $this->parent->fail(
            assertion: $str,
            reason: "missing key$m " . \implode(', ', $failedKeys),
        );
    }

    #[\Override]
    public function isList(string $message = ''): static
    {
        if (\array_is_list($this->value)) {
            $this->parent->success('is list');
            return $this;
        }

        throw $this->parent->fail(
            assertion: 'is list',
            reason: 'array keys are not sequential integers starting from 0',
            context: $message,
        );
    }
}
