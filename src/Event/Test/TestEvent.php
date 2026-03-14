<?php

declare(strict_types=1);

namespace Testo\Event\Test;

use Testo\Core\Context\TestInfo;

/**
 * Test related event.
 *
 * @api
 */
abstract readonly class TestEvent
{
    public function __construct(
        public TestInfo $testInfo,
    ) {}
}
