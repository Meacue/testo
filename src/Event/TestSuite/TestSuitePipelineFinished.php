<?php

declare(strict_types=1);

namespace Testo\Event\TestSuite;

use Testo\Core\Context\SuiteInfo;
use Testo\Core\Context\SuiteResult;

/**
 * Event triggered after the test suite pipeline (suite interceptors) has finished executing.
 *
 * This is the last event in the test suite lifecycle, fired after all suite interceptors have completed.
 *
 * @psalm-immutable
 */
final class TestSuitePipelineFinished extends TestSuiteEvent
{
    public function __construct(
        SuiteInfo $suiteInfo,
        public readonly SuiteResult $suiteResult,
    ) {
        parent::__construct($suiteInfo);
    }
}
