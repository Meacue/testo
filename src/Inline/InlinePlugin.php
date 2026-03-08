<?php

declare(strict_types=1);

namespace Testo\Inline;

use Testo\Application\Config\PluginConfigurator;
use Testo\Common\Container;
use Testo\Pipeline\InterceptorProvider;

/**
 * @api
 */
final class InlinePlugin implements PluginConfigurator
{
    public function configure(Container $container): void
    {
        $container->get(InterceptorProvider::class);
    }
}
