<?php

declare(strict_types=1);

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;

/**
 * Test suites for Data component.
 */
return [
    new SuiteConfig(
        name: 'Data/Unit',
        location: new FinderConfig(
            include: [__DIR__ . '/Unit'],
            exclude: [__DIR__ . '/Unit/Fixture'],
        ),
    ),
    new SuiteConfig(
        name: 'Data/Self',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
    ),
];
