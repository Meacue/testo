<?php

declare(strict_types=1);

use Testo\Config\FinderConfig;
use Testo\Config\SuiteConfig;

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
