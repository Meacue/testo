<?php

declare(strict_types=1);

namespace Tests\Common\Unit;

use Testo\Application\Internal\MessengerHub;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Common\ErrorReporter;
use Testo\Common\Messenger;
use Testo\Core\Log\Level;
use Testo\Test;
use Tests\Common\Stub\SpyDispatcher;

#[Test]
#[Covers(ErrorReporter::class)]
final class ErrorReporterTest
{
    public function reportRecordsSingleStderrMessageAtErrorLevelByDefault(): void
    {
        $spy = new SpyDispatcher();
        (new ErrorReporter(new MessengerHub($spy)))->report(new \RuntimeException('boom'));

        $messages = $spy->messages();
        Assert::count($messages, 1);
        Assert::same($messages[0]->message->channel, Messenger::CHANNEL_STDERR);
        Assert::same($messages[0]->message->level, Level::Error);
    }

    public function reportHonoursCustomLevelAndChannel(): void
    {
        $spy = new SpyDispatcher();
        (new ErrorReporter(new MessengerHub($spy)))->report(new \RuntimeException('x'), Level::Debug, 'custom');

        $message = $spy->messages()[0]->message;
        Assert::same($message->channel, 'custom');
        Assert::same($message->level, Level::Debug);
    }

    public function formatIncludesClassMessageAndPreviousChain(): void
    {
        $content = ErrorReporter::format(
            new \LogicException('outer', previous: new \RuntimeException('inner')),
        );

        Assert::string($content)->contains(\LogicException::class)->contains('outer');
        Assert::string($content)->contains('Caused by:')->contains('inner');
    }
}
