<?php

declare(strict_types=1);

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\Plugin\SuitePlugins;
use Testo\Application\Config\SuiteConfig;
use Testo\Bench\BenchmarkPlugin;
use Testo\Inline\InlineTestPlugin;

return new ApplicationConfig(
    suites: \array_merge(
        [
            new SuiteConfig(
                name: 'SRC',
                location: new FinderConfig(
                    include: ['src'],
                ),
                plugins: SuitePlugins::only(
                    new InlineTestPlugin(),
                    new BenchmarkPlugin(),
                ),
            ),
        ],
        # If running in CI, skip the sandbox
        \filter_var(dump(\getenv('TESTO_CI')), FILTER_VALIDATE_BOOLEAN) ? [] : [
            new SuiteConfig(
                name: 'sandbox',
                location: new FinderConfig(
                    include: ['tests/Testo'],
                ),
            ),
        ],
        require 'tests/Assert/suites.php',
        require 'tests/Common/suites.php',
        require 'tests/Lifecycle/suites.php',
        require 'tests/Data/suites.php',
        require 'tests/Bench/suites.php',
        require 'tests/Application/suites.php',
        require 'tests/Output/suites.php',
        require 'tests/Test/suites.php',
    ),
);
