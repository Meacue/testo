<?php

declare(strict_types=1);

namespace Testo\Core\Context;

use Testo\Core\Definition\CaseDefinitions;
use Testo\Core\Internal\Attributed;

/**
 * @api
 */
final readonly class SuiteInfo
{
    use Attributed;

    /**
     * @param non-empty-string $name
     * @param array<non-empty-string, mixed> $attributes
     */
    public function __construct(
        public string $name,
        public CaseDefinitions $testCases,
        public array $attributes = [],
    ) {}
}
