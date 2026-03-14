<?php

declare(strict_types=1);

namespace Testo\Core\Context;

use Testo\Core\Common\AttributedTrait;
use Testo\Core\Definition\CaseDefinitions;

/**
 * @api
 */
final class SuiteInfo
{
    use AttributedTrait;

    /**
     * @param array<non-empty-string, mixed> $attributes
     */
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
        public readonly CaseDefinitions $testCases,
        array $attributes = [],
    ) {
        $this->attributes = $attributes;
    }
}
