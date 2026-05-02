<?php

declare(strict_types=1);

namespace Testo\Core\Context;

use Testo\Core\Internal\Attributed;
use Testo\Core\Definition\TestDefinition;

/**
 * Information about run test.
 *
 * @api
 */
final class TestInfo
{
    use Attributed;

    /**
     * @param array<non-empty-string, mixed> $attributes
     */
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
        public readonly CaseInfo $caseInfo,
        public readonly TestDefinition $testDefinition,

        /**
         * Arguments to pass to the test method.
         * @var array<array-key, mixed>
         */
        public readonly array $arguments = [],
        array $attributes = [],
    ) {
        $this->attributes = $attributes;
    }

    public function with(
        ?array $arguments = null,
    ): self {
        return new self(
            name: $this->name,
            caseInfo: $this->caseInfo,
            testDefinition: $this->testDefinition,
            arguments: $arguments ?? $this->arguments,
            attributes: $this->attributes,
        );
    }
}
