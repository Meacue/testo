<?php

declare(strict_types=1);

namespace Testo\Assert\DataType\Json;

/**
 * Assertion utilities for integer data type.
 */
interface JsonStructure extends JsonCommon
{
    /**
     * Assert that the count of the given value matches the expected count.
     *
     * @param int<0, max> $count The expected count.
     *
     * @deprecated To be implemented
     */
    public function count(int $count, string $message = ''): static;

    /**
     * Assert that the specified path in the JSON structure meets the conditions defined in the callback.
     *
     * @param non-empty-string $path The JSON path to assert.
     * @param callable(JsonAbstract): mixed $callback A callback function that receives an AssertJson
     *        for the value at the specified path.
     *
     * @deprecated To be implemented
     */
    public function assertPath(string $path, callable $callback): static;
}
