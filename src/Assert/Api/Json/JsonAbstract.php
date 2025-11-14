<?php

declare(strict_types=1);

namespace Testo\Assert\Api\Json;

/**
 * Assertion methods for unified JSON data types.
 */
interface JsonAbstract extends JsonArray, JsonObject
{
    /**
     * Asserts that the JSON string has a maximum depth.
     *
     * @param int<1, max> $expected The expected maximum depth.
     *
     * @deprecated To be implemented
     */
    public function maxDepth(int $expected): static;

    /**
     * Asserts that the JSON string represents a valid JSON structure (object or array).
     *
     * @deprecated To be implemented
     */
    public function isStructure(): JsonStructure;

    /**
     * Asserts that the JSON string represents a valid JSON object.
     *
     * @deprecated To be implemented
     */
    public function isObject(): JsonObject;

    /**
     * Asserts that the JSON string represents a valid JSON array.
     *
     * @deprecated To be implemented
     */
    public function isArray(): JsonArray;

    /**
     * Asserts that the JSON string represents a primitive value (string, number, boolean, null).
     *
     * @deprecated To be implemented
     */
    public function isPrimitive(): JsonCommon;

    /**
     * Assert that the JSON array or object is empty.
     *
     * @deprecated To be implemented
     */
    public function empty(): JsonCommon;
}
