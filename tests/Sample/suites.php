<?php

declare(strict_types=1);

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;

/**
 * Test suites for Sample component.
 */
return [
    new SuiteConfig(
        name: 'Sample: Self Testing',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
    ),
];
