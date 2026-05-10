<?php

declare(strict_types=1);

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;

/**
 * Test suites for Retry component.
 */
return [
    new SuiteConfig(
        name: 'Retry/Unit',
        location: new FinderConfig(
            include: [__DIR__ . '/Unit'],
        ),
    ),
    new SuiteConfig(
        name: 'Retry/Feature',
        location: new FinderConfig(
            include: [__DIR__ . '/Feature'],
        ),
    ),
    new SuiteConfig(
        name: 'Retry/Self',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
    ),
];
