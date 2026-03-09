<?php

declare(strict_types=1);

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Bench\BenchWith;
use Testo\Inline\InlinePlugin;

return [
    new SuiteConfig(
        name: 'Bench/Inline',
        location: new FinderConfig(
            include: [
                \dirname((new \ReflectionClass(BenchWith::class))->getFileName()),
            ],
        ),
        plugins: [
            InlinePlugin::class,
        ],
    ),
    new SuiteConfig(
        name: 'Bench/Self',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
        plugins: [
            InlinePlugin::class,
        ],
    ),
];
