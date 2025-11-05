<?php

declare(strict_types=1);

namespace Testo\Assert\State;

use Testo\Expect;

/**
 * Failure for the {@see Expect::leaks()} expectation.
 */
final class ExpectLeaksFailure extends AssertException
{
    /**
     * @param non-empty-array<array-key, class-string> $array Array [alias => class name]
     */
    public static function fromClassArray(array $array, string $message): self
    {
        # Collect all records from the map
        $records = [];

        foreach ($array as $k => $class) {
            $records[] = \is_string($k) ? $k : $class;
        }

        return new self(
            assertion: 'Objects not cached: ' . \implode(', ', $records),
            context: $message,
            details: '',
        );
    }
}
