<?php

declare(strict_types=1);

namespace Tests\Core\Log;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Log\Level;
use Testo\Core\Log\Message;
use Testo\Test;

#[Test]
#[Covers(Message::class)]
final class MessageTest
{
    public function storesAllFields(): void
    {
        $message = new Message(1.5, 'stdout', Level::Warning, 'hello');

        Assert::same($message->time, 1.5);
        Assert::same($message->channel, 'stdout');
        Assert::same($message->level, Level::Warning);
        Assert::same($message->content, 'hello');
    }

    public function contextDefaultsToEmptyArray(): void
    {
        Assert::same((new Message(1.0, 'c', Level::Info, 'x'))->context, []);
    }

    public function keepsProvidedContext(): void
    {
        Assert::same((new Message(1.0, 'c', Level::Info, 'x', ['k' => 'v']))->context, ['k' => 'v']);
    }
}
