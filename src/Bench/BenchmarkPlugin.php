<?php

declare(strict_types=1);

namespace Testo\Bench;

use Testo\Application\Config\PluginConfigurator;
use Testo\Bench\Internal\Pipeline\BenchFinder;
use Testo\Common\Container;
use Testo\Pipeline\InterceptorCollector;

/**
 * Plugin that enables inline benchmarks feature.
 *
 * @see BenchWith
 * @api
 */
final readonly class BenchmarkPlugin implements PluginConfigurator
{
    #[\Override]
    public function configure(Container $container): void
    {
        $container->get(InterceptorCollector::class)->addInterceptor(BenchFinder::class);
    }
}
