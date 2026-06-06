<?php

declare(strict_types=1);

namespace Tests\Application\Unit\Internal;

use Testo\Application\Internal\EventDispatcher;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Common\Messenger;
use Testo\Core\Log\Level;
use Testo\Event\Message\MessageReceived;
use Testo\Test;
use Tests\Application\Stub\DummyEvent;

#[Test]
#[Covers(EventDispatcher::class)]
final class EventDispatcherTest
{
    public function faultyListenerIsReportedToStderrAndChainContinues(): void
    {
        $dispatcher = new EventDispatcher();
        $calls = [];
        $received = [];

        $dispatcher->addListener(
            MessageReceived::class,
            static function (MessageReceived $event) use (&$received): void {
                $received[] = $event;
            },
        );
        $dispatcher->addListener(DummyEvent::class, static function () use (&$calls): void {
            $calls[] = 'first';
            throw new \RuntimeException('boom');
        });
        $dispatcher->addListener(DummyEvent::class, static function () use (&$calls): void {
            $calls[] = 'second';
        });

        $dispatcher->dispatch(new DummyEvent());

        # The throwing listener did not abort the chain — the next listener still ran.
        Assert::same($calls, ['first', 'second']);

        # ...and the failure surfaced as a single stderr message.
        Assert::count($received, 1);
        Assert::same($received[0]->message->channel, Messenger::CHANNEL_STDERR);
        Assert::same($received[0]->message->level, Level::Error);
        Assert::string($received[0]->message->content)->contains('boom');
    }

    public function reportingIsGuardedAgainstInfiniteRecursion(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(MessageReceived::class, static function (): void {
            throw new \RuntimeException('the report listener itself fails');
        });
        $dispatcher->addListener(DummyEvent::class, static function (): void {
            throw new \RuntimeException('boom');
        });

        # A failing report-listener must hit the stderr-stream fallback, not recurse forever.
        $event = new DummyEvent();
        Assert::same($dispatcher->dispatch($event), $event);
    }
}
