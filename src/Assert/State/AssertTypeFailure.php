<?php

declare(strict_types=1);

namespace Testo\Assert\State;

use Testo\Assert\Support;

/**
 * Failure for type assertions.
 */
final class AssertTypeFailure extends AssertException
{
    /**
     * @param non-empty-string $type The expected type name.
     * @param mixed $value The actual value.
     */
    public static function create(string $type, mixed $value, string $message = ''): self
    {
        return new self(
            assertion: \sprintf(
                "Expected type %s, got %s",
                $type,
                Support::stringify($value),
            ),
            context: $message,
            details: '',
        );
    }
}
