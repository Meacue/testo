<?php

declare(strict_types=1);

namespace Tests\Test\Unit;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Test;
use Testo\Test\Skip;

/**
 * @see Skip
 */
#[Test]
#[Covers(Skip::class)]
final class SkipAttributeTest
{
    public function defaultReasonIsEmpty(): void
    {
        $skip = new Skip();

        Assert::same($skip->reason, '');
    }

    public function customReason(): void
    {
        $skip = new Skip('flaky on CI, see ISSUE-123');

        Assert::same($skip->reason, 'flaky on CI, see ISSUE-123');
    }

    public function namedReasonArgument(): void
    {
        $skip = new Skip(reason: 'named');

        Assert::same($skip->reason, 'named');
    }

    public function targetsClassMethodAndFunction(): void
    {
        $flags = self::attributeFlags();

        Assert::same($flags & \Attribute::TARGET_CLASS, \Attribute::TARGET_CLASS);
        Assert::same($flags & \Attribute::TARGET_METHOD, \Attribute::TARGET_METHOD);
        Assert::same($flags & \Attribute::TARGET_FUNCTION, \Attribute::TARGET_FUNCTION);
    }

    /**
     * A skip carries a single reason — a second attribute on the same target has nowhere
     * to go, so PHP itself rejects the duplicate at reflection time.
     */
    public function isNotRepeatable(): void
    {
        Assert::same(self::attributeFlags() & \Attribute::IS_REPEATABLE, 0);
    }

    private static function attributeFlags(): int
    {
        $attributes = (new \ReflectionClass(Skip::class))->getAttributes(\Attribute::class);

        /** @var \Attribute $attribute */
        $attribute = $attributes[0]->newInstance();

        return $attribute->flags;
    }
}
