<?php

declare(strict_types=1);

namespace Testo\Output\JUnit\Internal;

use Testo\Core\Value\Status;

/**
 * Captured `<testcase>` payload produced from a single `TestResult`.
 *
 * @internal
 */
final readonly class JUnitCaseNode
{
    public function __construct(
        /** @var non-empty-string */
        public string $name,
        public string $classname,
        public ?string $file,
        public ?int $line,
        public float $time,
        public Status $status,
        public ?JUnitCaseOutcome $outcome,
    ) {}
}
