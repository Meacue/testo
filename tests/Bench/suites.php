<?php

declare(strict_types=1);

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Bench\BenchmarkPlugin;
use Testo\Bench\BenchWith;
use Testo\Inline\TestInlinePlugin;

return [
    new SuiteConfig(
        name: 'Bench/Inline',
        location: new FinderConfig(
            include: [
                \dirname((new \ReflectionClass(BenchWith::class))->getFileName()),
            ],
        ),
        plugins: [
            TestInlinePlugin::class,
        ],
    ),
    new SuiteConfig(
        name: 'Bench/Self',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
        plugins: [
            BenchmarkPlugin::class,
            TestInlinePlugin::class,
        ],
    ),
];
