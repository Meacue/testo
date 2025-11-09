<?php

declare(strict_types=1);

namespace Testo\Test\Event\Test;

use Testo\Test\Definition\TestDefinition;
use Testo\Test\Dto\TestInfo;

/**
 * Test related event.
 */
abstract class TestEvent
{
    public function __construct(
        public readonly TestDefinition $testDefinition,
        public readonly TestInfo $testInfo,
    ) {}
}
