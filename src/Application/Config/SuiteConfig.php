<?php

declare(strict_types=1);

namespace Testo\Application\Config;

use Internal\Path;
use Testo\Application\Config\Plugin\SuitePlugins;
use Testo\Common\PluginConfigurator;

/**
 * Test Suite configuration.
 */
final readonly class SuiteConfig
{
    /**
     * Configuration for locating test cases.
     */
    public FinderConfig $location;

    /**
     * @param non-empty-string $name A unique name for the test suite.
     * @param FinderConfig|list<non-empty-string|Path> $location Configuration for locating test cases.
     *        An array is a shortcut for `new FinderConfig(include: $location)`.
     * @param iterable<PluginConfigurator> $plugins List of plugins to load for this suite.
     *        An array of plugin instances is treated as {@see SuitePlugins::with()}.
     *        Use {@see SuitePlugins} facade for advanced configuration.
     */
    public function __construct(
        public string $name,
        FinderConfig|array $location,
        public iterable $plugins = [],
    ) {
        $this->location = $location instanceof FinderConfig
            ? $location
            : new FinderConfig(include: $location);
    }

    public function with(
        ?FinderConfig $location = null,
        iterable|null $plugins = null,
    ): self {
        return new self(
            name: $this->name,
            location: $location ?? $this->location,
            plugins: $plugins ?? $this->plugins,
        );
    }
}
