<?php

declare(strict_types=1);

namespace Testo\Assert\State;

use Testo\Expect;

/**
 * Failure for the {@see Expect::notLeaks()} expectation.
 */
final class ExpectNotLeaksFailure extends AssertException
{
    /**
     * @param array<array-key, \WeakReference<object>> $map The map of tracked objects.
     */
    public static function fromWeakReferences(array $map, string $message): self
    {
        # Collect all records from the map
        $records = [];

        foreach ($map as $k => $ref) {
            $obj = $ref->get();
            $obj === null or $records[] = \is_string($k) ? $k : $obj::class;
        }

        return new self(
            assertion: 'Memory leak detected for ' . \implode(', ', $records),
            context: $message,
            details: '',
        );
    }
}
