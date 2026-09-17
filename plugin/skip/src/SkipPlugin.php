<?php

declare(strict_types=1);

namespace Testo\Skip;

use Internal\Container\Container;
use Testo\Common\PluginConfigurator;
use Testo\Pipeline\InterceptorCollector;
use Testo\Skip;
use Testo\Skip\Internal\SkipLocatorInterceptor;

/**
 * Marks the `#[Skip]`-annotated tests as skipped at location time, so the case pipeline knows
 * ahead of the run which tests have no body to prepare for.
 *
 * Part of the default suite plugins. The Skipped result itself does not depend on the plugin:
 * {@see Skip} wires its own per-test interceptor, and the core reports a skipped test even without
 * one. Without the plugin the lifecycle hooks are unaware of the skip and run as for any test.
 *
 * @api
 */
final readonly class SkipPlugin implements PluginConfigurator
{
    #[\Override]
    public function configure(Container $container): void
    {
        $container->get(InterceptorCollector::class)->addInterceptor(new SkipLocatorInterceptor());
    }
}
