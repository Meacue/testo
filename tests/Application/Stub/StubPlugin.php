<?php

declare(strict_types=1);

namespace Tests\Application\Stub;

use Testo\Common\Container;
use Testo\Common\PluginConfigurator;

final readonly class StubPlugin implements PluginConfigurator
{
    public function __construct(
        public string $name = 'default',
    ) {}

    #[\Override]
    public function configure(Container $container): void
    {
        // no-op
    }
}
