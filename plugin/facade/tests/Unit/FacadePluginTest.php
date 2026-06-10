<?php

declare(strict_types=1);

namespace Tests\Facade\Unit;

use Internal\Container\Container;
use Testo\Assert;
use Testo\Common\PluginConfigurator;
use Testo\Facade\FacadePlugin;
use Testo\Facade\Internal\StaticState;
use Testo\Test;

#[Test]
final class FacadePluginTest
{
    public function pluginIsConfigurator(): void
    {
        Assert::instanceOf(new FacadePlugin(), PluginConfigurator::class);
    }

    public function suiteContainerIsExposed(): void
    {
        # FacadePlugin is part of the default suite plugins, so it has already stored the container.
        Assert::instanceOf(StaticState::current(), Container::class);
    }
}
