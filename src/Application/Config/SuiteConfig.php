<?php

declare(strict_types=1);

namespace Testo\Application\Config;

/**
 * Test Suite configuration.
 */
final class SuiteConfig
{
    public function __construct(
        /**
         * @var non-empty-string A unique name for the test suite.
         */
        public readonly string $name,

        /**
         * @var FinderConfig Configuration for locating test cases.
         */
        public readonly FinderConfig $location,

        /**
         * @var list<PluginConfigurator|class-string<PluginConfigurator>> List of plugins to load for this suite.
         */
        public array $plugins = [],
    ) {}
}
