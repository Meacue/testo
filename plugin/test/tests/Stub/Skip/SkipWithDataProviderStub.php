<?php

declare(strict_types=1);

namespace Tests\Test\Stub\Skip;

use Testo\Data\DataProvider;
use Testo\Test;
use Testo\Test\Skip;

final class SkipWithDataProviderStub
{
    public static bool $providerCalled = false;

    #[Test]
    #[Skip('data-driven test is parked as a whole')]
    #[DataProvider('provide')]
    public function parked(int $value): void
    {
        throw new \LogicException('Must never run: the test is parked.');
    }

    public static function provide(): iterable
    {
        self::$providerCalled = true;
        yield [1];
        yield [2];
    }
}
