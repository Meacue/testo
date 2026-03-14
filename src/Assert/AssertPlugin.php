<?php

declare(strict_types=1);

namespace Testo\Assert;

use Testo\Application\Config\PluginConfigurator;
use Testo\Assert;
use Testo\Assert\Interceptor\AssertCollectorInterceptor;
use Testo\Assert\Interceptor\ExpectationsInterceptor;
use Testo\Common\Container;
use Testo\Expect;
use Testo\Pipeline\InterceptorCollector;

/**
 * @see Assert
 * @see Expect
 *
 * @api
 */
final readonly class AssertPlugin implements PluginConfigurator
{
    #[\Override]
    public function configure(Container $container): void
    {
        $collector = $container->get(InterceptorCollector::class);
        $collector->addInterceptor(new AssertCollectorInterceptor());
        $collector->addInterceptor(new ExpectationsInterceptor());
    }
}
