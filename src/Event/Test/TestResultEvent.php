<?php

declare(strict_types=1);

namespace Testo\Event\Test;

use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;

/**
 * Test result related event.
 */
abstract class TestResultEvent extends TestEvent
{
    public function __construct(
        TestInfo $testInfo,
        public readonly TestResult $testResult,
    ) {
        parent::__construct($testInfo);
    }
}
