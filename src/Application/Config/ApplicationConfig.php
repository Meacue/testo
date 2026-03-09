<?php

declare(strict_types=1);

namespace Testo\Application\Config;

/**
 * Test Suite configuration.
 *
 * @api
 */
final readonly class ApplicationConfig
{
    public function __construct(
        /**
         * Source code location.
         */
        public ?FinderConfig $src = null,

        /**
         * Specify one or more Test Suites to be executed.
         *
         * @var non-empty-list<SuiteConfig>
         */
        public array $suites = [
            new SuiteConfig(
                name: 'default',
                location: new FinderConfig(['tests']),
            ),
        ],

        /**
         * List of plugins to load.
         *
         * @see DefaultServicesConfig for default services bindings, which can be overridden.
         *
         * @var list<PluginConfigurator|class-string<PluginConfigurator>>
         */
        public array $plugins = [],
    ) {
        # Validate suite configs
        $suites === [] and throw new \InvalidArgumentException('At least one test suite must be defined.');
        \array_walk($suites, static fn(mixed $suite) => $suite instanceof SuiteConfig
            or throw new \InvalidArgumentException(
                'Each suite must be an instance of SuiteConfig.',
            ));
        \array_walk($plugins, static fn(mixed $plugin) => \is_a($plugin, PluginConfigurator::class, true)
            or throw new \InvalidArgumentException(
                'Each plugin must be an instance of PluginConfigurator or a class name string.',
            ));
    }
}
