<?php

declare(strict_types=1);

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Inline\TestInlinePlugin;

/**
 * Test suites for Data component.
 */
return [
    new SuiteConfig(
        name: 'Data: Self Testing',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
        plugins: [
            TestInlinePlugin::class,
        ],
    ),
];
