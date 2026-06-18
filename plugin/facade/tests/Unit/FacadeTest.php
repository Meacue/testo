<?php

declare(strict_types=1);

namespace Tests\Facade\Unit;

use Psr\Log\LoggerInterface;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Facade\Exception\ContainerNotFound;
use Testo\Facade\FacadePlugin;
use Testo\Test;

#[Test]
#[Covers(\Testo::class)]
final class FacadeTest
{
    public function channelReturnsLoggerBoundToName(): void
    {
        $channel = \Testo::logger('facade-test');

        Assert::instanceOf($channel, LoggerInterface::class);
        Assert::same($channel->name, 'facade-test');
    }

    /**
     * The exception names the {@see FacadePlugin} that must be installed to use the facade.
     */
    #[Covers(ContainerNotFound::class)]
    public function containerNotFoundNamesThePlugin(): void
    {
        Assert::string((new ContainerNotFound())->getMessage())->contains(FacadePlugin::class);
    }
}
