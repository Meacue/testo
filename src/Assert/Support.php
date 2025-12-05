<?php

declare(strict_types=1);

namespace Testo\Assert;

final class Support
{
    /**
     * Convert a value to a string for error messages.
     */
    public static function stringify(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            $value === true => 'true',
            $value === false => 'false',
            \is_string($value) => \strlen($value) > 64
                ? 'string(' . \strlen($value) . ')'
                : '"' . \str_replace('"', '\\"', $value) . '"',
            \is_array($value) => 'array(' . \count($value) . ')',
            \is_resource($value) => 'resource',
            \is_object($value) => $value::class,
            default => (string) $value,
        };
    }

    /**
     * Counts the number of elements in the given iterable.
     */
    public static function countIterable(iterable $value): int
    {
        // if Countable
        if (\is_array($value) || $value instanceof \Countable) {
            return \count($value);
        }

        // if Traversable
        $count = 0;
        foreach ($value as $_) {
            $count++;
        }

        return $count;
    }
}
