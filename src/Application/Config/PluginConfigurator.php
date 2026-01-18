<?php

declare(strict_types=1);

namespace Testo\Application\Config;

use Testo\Common\Container;

/**
 * Plugin config handler.
 */
interface PluginConfigurator
{
    public function configure(Container $container): void;
}
