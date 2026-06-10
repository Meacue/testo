<?php

declare(strict_types=1);

namespace Testo\Facade;

use Internal\Container\Container;
use Testo\Common\PluginConfigurator;
use Testo\Facade\Internal\StaticState;

/**
 * Exposes the suite's DI container to the static {@see \Testo}.
 *
 * @see \Testo
 *
 * @api
 */
final readonly class FacadePlugin implements PluginConfigurator
{
    #[\Override]
    public function configure(Container $container): void
    {
        StaticState::$container = $container;
    }
}
