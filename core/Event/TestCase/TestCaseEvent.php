<?php

declare(strict_types=1);

namespace Testo\Event\TestCase;

use Testo\Core\Context\CaseInfo;

/**
 * Test case related event.
 *
 * @psalm-immutable
 * @api
 */
abstract readonly class TestCaseEvent
{
    public function __construct(
        public CaseInfo $caseInfo,
    ) {}
}
