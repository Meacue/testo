<?php

declare(strict_types=1);

namespace Testo\Event\TestSuite;

use Testo\Core\Context\SuiteInfo;

/**
 * Test suite related event.
 */
abstract class TestSuiteEvent
{
    public function __construct(
        public readonly SuiteInfo $suiteInfo,
    ) {}
}
