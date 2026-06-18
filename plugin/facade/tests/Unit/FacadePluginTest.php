<?php

declare(strict_types=1);

namespace Tests\Facade\Unit;

use Internal\Container\Container;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Common\PluginConfigurator;
use Testo\Facade\FacadePlugin;
use Testo\Facade\Internal\StaticState;
use Testo\Test;

#[Test]
#[Covers(FacadePlugin::class)]
#[Covers(StaticState::class)]
final class FacadePluginTest
{
    public function pluginIsConfigurator(): void
    {
        Assert::instanceOf(new FacadePlugin(), PluginConfigurator::class);
    }

    /**
     * The running suite installs {@see FacadePlugin} by default, so the container is already exposed.
     */
    public function suiteContainerIsExposed(): void
    {
        Assert::instanceOf(StaticState::current(), Container::class);
    }
}
