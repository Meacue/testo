<?php

declare(strict_types=1);

namespace Testo\Application;

use Internal\Path;
use Psr\EventDispatcher\EventDispatcherInterface;
use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\DefaultServicesConfig;
use Testo\Application\Config\Internal\ConfigInflector;
use Testo\Application\Config\PluginConfigurator;
use Testo\Application\Internal\ObjectContainer;
use Testo\Application\Internal\Runner\SuiteRunner;
use Testo\Application\Internal\SuiteProvider;
use Testo\Application\Value\Filter;
use Testo\Application\Value\RunResult;
use Testo\Common\Container;
use Testo\Core\Context\SuiteResult;
use Testo\Core\Value\Status;
use Testo\Event\Framework\SessionFinished;
use Testo\Event\Framework\SessionStarting;
use Testo\Event\Framework\WorkerFinished;
use Testo\Event\Framework\WorkerStarting;

final readonly class Application
{
    private function __construct(
        private ObjectContainer $container,
    ) {
        self::applyPlugins($container, [DefaultServicesConfig::class]);
    }

    /**
     * Create the application instance with the provided configuration.
     */
    public static function createFromConfig(
        ApplicationConfig $config,
    ): self {
        $container = new ObjectContainer();
        $container->set($config);
        return new self($container);
    }

    /**
     * Create the application instance from ENV, CLI arguments, and config file.
     *
     * @param Path|null $configFile Path to config file
     * @param array<string, mixed> $inputOptions Command-line options
     * @param array<string, mixed> $inputArguments Command-line arguments
     * @param array<string, string> $environment Environment variables
     */
    public static function createFromInput(
        ?Path $configFile = null,
        array $inputOptions = [],
        array $inputArguments = [],
        array $environment = [],
    ): self {
        $container = new ObjectContainer();
        $args = [
            'env' => $environment,
            'inputArguments' => $inputArguments,
            'inputOptions' => $inputOptions,
        ];

        # Bind reading provided config file
        $configFile === null or $container
            ->bind(ApplicationConfig::class, function () use ($configFile): ApplicationConfig {
                $cfg = include $configFile;
                $cfg instanceof ApplicationConfig or throw new \InvalidArgumentException(
                    \sprintf(
                        'Configuration file %s must return an instance of %s, %s returned.',
                        $configFile,
                        ApplicationConfig::class,
                        \is_object($cfg) ? \get_class($cfg) : \gettype($cfg),
                    ),
                );
                return $cfg;
            });

        # Register Config inflector
        $container->addInflector($container->make(ConfigInflector::class, $args));
        $container->bind(Filter::class, Filter::fromScope(...));

        return new self($container);
    }

    public function run(?Filter $filter = null): RunResult
    {
        return $this->container->scope(static function (Container $container) use ($filter): RunResult {
            $filter === null or $this->container->set($filter);
            $appConfig = $container->get(ApplicationConfig::class);
            $filter ??= $container->get(Filter::class);
            self::applyPlugins($container, $appConfig->plugins);

            $dispatcher = $container->get(EventDispatcherInterface::class);
            $dispatcher->dispatch(new SessionStarting());
            $dispatcher->dispatch(new WorkerStarting());

            $suiteProvider = $container->get(SuiteProvider::class);
            $status = Status::Passed;
            $duration = \microtime(true);

            # Iterate and run Test Suites
            $suiteResults = [];
            foreach ($appConfig->suites as $config) {
                # Resolve a Test Suite from config and
                # run a separate container scope to isolate services and plugins.
                $suiteResult = $container->scope(
                    static function (Container $container) use ($filter, $config, $suiteProvider): ?SuiteResult {
                        # Apply plugins first to have all interceptors before suite files scanning
                        self::applyPlugins($container, $config->plugins);

                        if (null === $suite = $suiteProvider->findSuite($config)) {
                            return null;
                        }

                        $suiteRunner = $container->get(SuiteRunner::class);
                        return $suiteRunner->runSuite($suite, $filter);
                    },
                );
                if ($suiteResult === null) {
                    continue;
                }

                $suiteResults[] = $suiteResult;
                $suiteResult->status->isFailure() and $status = Status::Failed;
            }

            $duration = \microtime(true) - $duration;
            $result = new RunResult($suiteResults, status: $status, duration: $duration);

            $dispatcher->dispatch(new WorkerFinished());
            $dispatcher->dispatch(new SessionFinished($result));
            return $result;
        });
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Apply plugin services to the container.
     *
     * @param list<PluginConfigurator|class-string<PluginConfigurator>> $plugins
     */
    private static function applyPlugins(Container $container, array $plugins): void
    {
        foreach ($plugins as $plugin) {
            ($plugin instanceof PluginConfigurator ? $plugin : $container->make($plugin))
                ->configure($container);
        }
    }
}
