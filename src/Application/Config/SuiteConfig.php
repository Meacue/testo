<?php

declare(strict_types=1);

namespace Testo\Application\Config;

/**
 * Test Suite configuration.
 */
final readonly class SuiteConfig
{
    public function __construct(
        /**
         * @var non-empty-string A unique name for the test suite.
         */
        public string $name,

        /**
         * @var FinderConfig Configuration for locating test cases.
         */
        public FinderConfig $location,

        /**
         * @var list<PluginConfigurator|class-string<PluginConfigurator>> List of plugins to load for this suite.
         */
        public array $plugins = [],
    ) {}

    public function with(
        ?FinderConfig $finder = null,
        ?array $plugins = null,
    ): self {
        return new self(
            name: $this->name,
            location: $finder ?? $this->location,
            plugins: $plugins ?? $this->plugins,
        );
    }
}
