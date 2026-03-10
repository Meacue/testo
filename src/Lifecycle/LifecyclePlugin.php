<?php

declare(strict_types=1);

namespace Testo\Lifecycle;

use Testo\Application\Config\PluginConfigurator;
use Testo\Common\Container;
use Testo\Lifecycle\Internal\LifecycleInterceptor;
use Testo\Pipeline\InterceptorCollector;

/**
 * Plugin that enables lifecycle attributed methods.
 *
 * Lifecycle methods are methods that are executed before or after tests.
 * They are defined by the following attributes:
 * - {@see BeforeAll}
 * - {@see BeforeEach}
 * - {@see AfterEach}
 * - {@see AfterAll}
 *
 * @api
 */
final readonly class LifecyclePlugin implements PluginConfigurator
{
    public function configure(Container $container): void
    {
        $container->get(InterceptorCollector::class)->addInterceptor(LifecycleInterceptor::class);
    }
}
