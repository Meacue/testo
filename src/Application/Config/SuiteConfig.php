<?php

declare(strict_types=1);

namespace Testo\Application\Config;

use Testo\Application\Config\Plugin\SuitePlugins;

/**
 * Test Suite configuration.
 */
final readonly class SuiteConfig
{
    public function __construct(
        /**
         * A unique name for the test suite.
         *
         * @var non-empty-string
         */
        public string $name,

        /**
         * Configuration for locating test cases.
         *
         * @var FinderConfig
         */
        public FinderConfig $location,

        /**
         * List of plugins to load for this suite.
         *
         * An array of plugin instances is treated as {@see SuitePlugins::with()}.
         * Use {@see SuitePlugins} facade for advanced configuration.
         *
         * @var iterable<PluginConfigurator>
         */
        public iterable $plugins = [],
    ) {}

    public function with(
        ?FinderConfig $finder = null,
        iterable|null $plugins = null,
    ): self {
        return new self(
            name: $this->name,
            location: $finder ?? $this->location,
            plugins: $plugins ?? $this->plugins,
        );
    }
}
