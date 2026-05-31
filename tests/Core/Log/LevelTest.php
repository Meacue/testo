<?php

declare(strict_types=1);

namespace Tests\Core\Log;

use Psr\Log\LogLevel;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Log\Level;
use Testo\Test;

#[Test]
#[Covers(Level::class)]
final class LevelTest
{
    public function valuesMatchPsr3LogLevels(): void
    {
        Assert::same(Level::Emergency->value, LogLevel::EMERGENCY);
        Assert::same(Level::Alert->value, LogLevel::ALERT);
        Assert::same(Level::Critical->value, LogLevel::CRITICAL);
        Assert::same(Level::Error->value, LogLevel::ERROR);
        Assert::same(Level::Warning->value, LogLevel::WARNING);
        Assert::same(Level::Notice->value, LogLevel::NOTICE);
        Assert::same(Level::Info->value, LogLevel::INFO);
        Assert::same(Level::Debug->value, LogLevel::DEBUG);
    }

    public function hasEightCases(): void
    {
        Assert::count(Level::cases(), 8);
    }

    public function tryFromKnownValue(): void
    {
        Assert::same(Level::tryFrom('debug'), Level::Debug);
    }

    public function tryFromUnknownValueReturnsNull(): void
    {
        Assert::null(Level::tryFrom('verbose'));
    }
}
