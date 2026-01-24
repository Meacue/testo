<?php

declare(strict_types=1);

namespace Testo\Testing\Attribute;

use Internal\Path;

/**
 * Configure Test Suite for the testing tools.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class TestingSuite
{
    public function __construct(
        /**
         * @var string|Path Stub directory or file path.
         */
        public readonly string|Path $path,
    ) {}
}
