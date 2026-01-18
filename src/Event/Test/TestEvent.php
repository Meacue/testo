<?php

declare(strict_types=1);

namespace Testo\Event\Test;

use Testo\Core\Context\TestInfo;

/**
 * Test related event.
 */
abstract class TestEvent
{
    public function __construct(
        public readonly TestInfo $testInfo,
    ) {}
}
