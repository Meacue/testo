<?php

declare(strict_types=1);

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Lifecycle\LifecyclePlugin;

/**
 * Test suites for Lifecycle component.
 */
return [
    new SuiteConfig(
        name: 'Lifecycle: Self Testing',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
        plugins: [
            LifecyclePlugin::class,
        ],
    ),
];
