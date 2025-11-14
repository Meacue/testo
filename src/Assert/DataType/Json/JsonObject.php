<?php

declare(strict_types=1);

namespace Testo\Assert\DataType\Json;

/**
 * Assertion utilities for JSON object data type.
 */
interface JsonObject extends JsonStructure
{
    /**
     * Assert that the JSON object has the specified keys.
     *
     * @param array<string>|string $keys The keys to check for existence.
     */
    public function hasKeys(array|string $keys, string $message = ''): JsonObject;
}
