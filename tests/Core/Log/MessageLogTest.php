<?php

declare(strict_types=1);

namespace Tests\Core\Log;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Log\Level;
use Testo\Core\Log\Message;
use Testo\Core\Log\MessageLog;
use Testo\Test;

#[Test]
#[Covers(MessageLog::class)]
final class MessageLogTest
{
    public function emptyByDefault(): void
    {
        $log = new MessageLog();

        Assert::true($log->isEmpty());
        Assert::count($log, 0);
        Assert::same($log->all(), []);
    }

    public function keepsAllMessagesInOrder(): void
    {
        $log = self::log();

        Assert::false($log->isEmpty());
        Assert::count($log, 3);
        Assert::same(self::contents($log->all()), ['a', 'q', 'b']);
    }

    public function filtersByChannel(): void
    {
        Assert::same(self::contents(self::log()->channel('stdout')), ['a', 'b']);
    }

    public function filtersByLevel(): void
    {
        Assert::same(self::contents(self::log()->level(Level::Error)), ['b']);
    }

    public function isIterable(): void
    {
        $contents = [];
        foreach (self::log() as $message) {
            $contents[] = $message->content;
        }

        Assert::same($contents, ['a', 'q', 'b']);
    }

    public function reindexesMessagesToAList(): void
    {
        $log = new MessageLog([5 => new Message(1.0, 'c', Level::Info, 'x')]);
        Assert::array($log->all())->isList();
    }

    private static function log(): MessageLog
    {
        return new MessageLog([
            new Message(1.0, 'stdout', Level::Info, 'a'),
            new Message(2.0, 'sql', Level::Debug, 'q'),
            new Message(3.0, 'stdout', Level::Error, 'b'),
        ]);
    }

    /**
     * @param list<Message> $messages
     * @return list<string>
     */
    private static function contents(array $messages): array
    {
        return \array_map(static fn(Message $m): string => $m->content, $messages);
    }
}
