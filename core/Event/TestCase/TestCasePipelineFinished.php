<?php

declare(strict_types=1);

namespace Testo\Event\TestCase;

use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\CaseResult;

/**
 * Event triggered after the test case pipeline (case interceptors) has finished executing.
 *
 * This is the last event in the test case lifecycle, fired after all case interceptors have completed.
 *
 * @psalm-immutable
 * @api
 */
final readonly class TestCasePipelineFinished extends TestCaseEvent
{
    public function __construct(
        CaseInfo $caseInfo,
        public CaseResult $caseResult,
    ) {
        parent::__construct($caseInfo);
    }
}
