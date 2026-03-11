<?php

declare(strict_types=1);

namespace Testo\Application\Config;

use Testo\Application\Config\Plugin\ApplicationPlugins;

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
         * List of plugins to load at the application level (applied to all suites).
         *
         * An array of plugin instances is treated as {@see ApplicationPlugins::with()}.
         * Use {@see ApplicationPlugins} facade for advanced configuration.
         *
         * @see DefaultServicesConfig for default services bindings, which can be overridden.
         *
         * @var iterable<PluginConfigurator>
         */
        public iterable $plugins = [],
    ) {
        # Validate suite configs
        $suites === [] and throw new \InvalidArgumentException('At least one test suite must be defined.');
        \array_walk($suites, static fn(mixed $suite) => $suite instanceof SuiteConfig
            or throw new \InvalidArgumentException(
                'Each suite must be an instance of SuiteConfig.',
            ));
    }
}
