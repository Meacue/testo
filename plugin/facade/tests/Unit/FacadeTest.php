<?php

declare(strict_types=1);

namespace Tests\Facade\Unit;

use Psr\Log\LoggerInterface;
use Testo\Assert;
use Testo\Common\Messenger\Channel;
use Testo\Test;

#[Test]
final class FacadeTest
{
    public function channelReturnsLoggerBoundToName(): void
    {
        $channel = \Testo::logger('facade-test');

        Assert::instanceOf($channel, LoggerInterface::class);
        Assert::same($channel->name, 'facade-test');
    }
}
