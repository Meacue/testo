<?php

declare(strict_types=1);

namespace Testo\Common;

/**
 * Plugin config handler.
 */
interface PluginConfigurator
{
    public function configure(Container $container): void;
}
