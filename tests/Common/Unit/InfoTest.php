<?php

declare(strict_types=1);

namespace Tests\Common\Unit;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Common\Info;
use Testo\Test;

#[Test]
#[Covers(Info::class)]
final class InfoTest
{
    public function versionReturnsCachedValueOnRepeatedCalls(): void
    {
        $first = Info::version();
        $second = Info::version();

        Assert::same($first, $second);
    }

    public function versionMatchesRootEntryFromVersionJson(): void
    {
        $payload = \json_decode(\file_get_contents(Info::ROOT_DIR . '/resources/version.json'), true);

        Assert::same(Info::version(), $payload['.']);
    }

    public function nameConstantIsTesto(): void
    {
        Assert::same(Info::NAME, 'Testo');
    }
}
