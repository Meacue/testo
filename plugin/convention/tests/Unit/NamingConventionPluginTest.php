<?php

declare(strict_types=1);

namespace Tests\Convention\Unit;

use Testo\Assert;
use Testo\Convention\NamingConventionPlugin;
use Testo\Test;

#[Test]
final class NamingConventionPluginTest
{
    public function defaultsAreAccepted(): void
    {
        new NamingConventionPlugin();

        Assert::true(true);
    }

    public function customSuffixAccepted(): void
    {
        new NamingConventionPlugin(caseSuffix: 'Spec');

        Assert::true(true);
    }

    public function customPrefixAccepted(): void
    {
        new NamingConventionPlugin(testPrefix: 'it');

        Assert::true(true);
    }

    public function emptyCaseSuffixIsAllowed(): void
    {
        new NamingConventionPlugin(caseSuffix: '');

        Assert::true(true);
    }

    public function caseSuffixWithDashThrows(): void
    {
        self::assertThrowsInvalidArgument(
            static fn() => new NamingConventionPlugin(caseSuffix: 'Foo-Bar'),
            'Case suffix must be a valid PHP class name suffix.',
        );
    }

    public function caseSuffixWithSpecialCharThrows(): void
    {
        self::assertThrowsInvalidArgument(
            static fn() => new NamingConventionPlugin(caseSuffix: 'Foo$'),
            'Case suffix must be a valid PHP class name suffix.',
        );
    }

    public function emptyTestPrefixThrows(): void
    {
        self::assertThrowsInvalidArgument(
            static fn() => new NamingConventionPlugin(testPrefix: ''),
            'Test prefix must be a valid PHP method name prefix.',
        );
    }

    public function testPrefixStartingWithDigitThrows(): void
    {
        self::assertThrowsInvalidArgument(
            static fn() => new NamingConventionPlugin(testPrefix: '1foo'),
            'Test prefix must be a valid PHP method name prefix.',
        );
    }

    public function testPrefixWithDashThrows(): void
    {
        self::assertThrowsInvalidArgument(
            static fn() => new NamingConventionPlugin(testPrefix: 'it-does'),
            'Test prefix must be a valid PHP method name prefix.',
        );
    }

    private static function assertThrowsInvalidArgument(\Closure $action, string $expectedMessage): void
    {
        $thrown = null;
        try {
            $action();
        } catch (\InvalidArgumentException $e) {
            $thrown = $e;
        }

        Assert::instanceOf($thrown, \InvalidArgumentException::class);
        Assert::same($thrown->getMessage(), $expectedMessage);
    }
}
