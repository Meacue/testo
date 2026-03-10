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
         * @var list<PluginConfigurator|class-string<PluginConfigurator>>
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
