<?php

declare(strict_types=1);

namespace Testo\Output\JUnit\Internal;

/**
 * Encapsulates the optional child element of a `<testcase>` (one of
 * `<failure>`, `<error>`, `<skipped>`).
 *
 * @internal
 */
final readonly class JUnitCaseOutcome
{
    public function __construct(
        /**
         * @var non-empty-string XML element name: 'failure', 'error', or 'skipped'.
         */
        public string $element,
        public string $type,
        public string $message,
        public string $details,
    ) {}
}
