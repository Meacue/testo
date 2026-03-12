<?php

declare(strict_types=1);

namespace Tests\Application\Unit\Config;

use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Assert;
use Testo\Attribute\Test;
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
        Assert::same($finder, $config->location);
    }

    public function constructWithArrayNormalizesToFinderConfig(): void
    {
        // Act
        $config = new SuiteConfig(name: 'unit', location: [__DIR__]);

        // Assert
        Assert::instanceOf(FinderConfig::class, $config->location);
        Assert::same(1, \count($config->location->includes));
    }

    public function constructSetsName(): void
    {
        // Act
        $config = new SuiteConfig(name: 'my-suite', location: [__DIR__]);

        // Assert
        Assert::same('my-suite', $config->name);
    }

    public function constructDefaultParallelIsFalse(): void
    {
        // Act
        $config = new SuiteConfig(name: 'unit', location: [__DIR__]);

        // Assert
        Assert::false($config->parallel);
    }

    public function constructDefaultPluginsAreEmpty(): void
    {
        // Act
        $config = new SuiteConfig(name: 'unit', location: [__DIR__]);

        // Assert
        Assert::same([], $config->plugins);
    }

    public function withReturnsNewInstanceWithUpdatedLocation(): void
    {
        // Arrange
        $original = new SuiteConfig(name: 'unit', location: [__DIR__]);
        $newFinder = new FinderConfig([__DIR__]);

        // Act
        $updated = $original->with(location: $newFinder);

        // Assert
        Assert::same($newFinder, $updated->location);
        Assert::notSame($original, $updated);
    }

    public function withReturnsNewInstanceWithUpdatedParallel(): void
    {
        // Arrange
        $original = new SuiteConfig(name: 'unit', location: [__DIR__]);

        // Act
        $updated = $original->with(parallel: true);

        // Assert
        Assert::true($updated->parallel);
        Assert::false($original->parallel);
    }

    public function withReturnsNewInstanceWithUpdatedPlugins(): void
    {
        // Arrange
        $original = new SuiteConfig(name: 'unit', location: [__DIR__]);
        $plugin = new StubPlugin();

        // Act
        $updated = $original->with(plugins: [$plugin]);

        // Assert
        Assert::same([$plugin], $updated->plugins);
        Assert::same([], $original->plugins);
    }

    public function withPreservesUnchangedValues(): void
    {
        // Arrange
        $finder = new FinderConfig([__DIR__]);
        $original = new SuiteConfig(name: 'unit', location: $finder, parallel: true);

        // Act
        $updated = $original->with();

        // Assert
        Assert::same('unit', $updated->name);
        Assert::same($finder, $updated->location);
        Assert::true($updated->parallel);
    }
}
