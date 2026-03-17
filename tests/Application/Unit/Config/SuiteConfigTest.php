<?php

declare(strict_types=1);

namespace Tests\Application\Unit\Config;

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Assert;
use Testo\Test;
use Tests\Application\Stub\StubPlugin;

#[Test]
final class SuiteConfigTest
{
    public function constructWithFinderConfig(): void
    {
        // Arrange
        $finder = new FinderConfig([__DIR__]);

        // Act
        $config = new SuiteConfig(name: 'unit', location: $finder);

        // Assert
        Assert::same($config->location, $finder);
    }

    public function constructWithArrayNormalizesToFinderConfig(): void
    {
        // Act
        $config = new SuiteConfig(name: 'unit', location: [__DIR__]);

        // Assert
        Assert::instanceOf($config->location, FinderConfig::class);
        Assert::count($config->location->includes, 1);
    }

    public function constructSetsName(): void
    {
        // Act
        $config = new SuiteConfig(name: 'my-suite', location: [__DIR__]);

        // Assert
        Assert::same($config->name, 'my-suite');
    }

    public function constructDefaultPluginsAreEmpty(): void
    {
        // Act
        $config = new SuiteConfig(name: 'unit', location: [__DIR__]);

        // Assert
        Assert::same($config->plugins, []);
    }

    public function withReturnsNewInstanceWithUpdatedLocation(): void
    {
        // Arrange
        $original = new SuiteConfig(name: 'unit', location: [__DIR__]);
        $newFinder = new FinderConfig([__DIR__]);

        // Act
        $updated = $original->with(location: $newFinder);

        // Assert
        Assert::same($updated->location, $newFinder);
        Assert::notSame($updated, $original);
    }

    public function withReturnsNewInstanceWithUpdatedPlugins(): void
    {
        // Arrange
        $original = new SuiteConfig(name: 'unit', location: [__DIR__]);
        $plugin = new StubPlugin();

        // Act
        $updated = $original->with(plugins: [$plugin]);

        // Assert
        Assert::same($updated->plugins, [$plugin]);
        Assert::same($original->plugins, []);
    }

    public function withPreservesUnchangedValues(): void
    {
        // Arrange
        $finder = new FinderConfig([__DIR__]);
        $original = new SuiteConfig(name: 'unit', location: $finder);

        // Act
        $updated = $original->with();

        // Assert
        Assert::same($updated->name, 'unit');
        Assert::same($updated->location, $finder);
    }
}
