<?php

declare(strict_types=1);

namespace Tests\Application\Unit\Config;

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Assert;
use Testo\Assert\ExpectException;
use Testo\Attribute\Test;
use Tests\Application\Stub\StubPlugin;

#[Test]
final class ApplicationConfigTest
{
    public function constructWithDefaultSrcCreatesEmptyFinderConfig(): void
    {
        // Act
        $config = new ApplicationConfig();

        // Assert
        Assert::instanceOf($config->src, FinderConfig::class);
        Assert::same($config->src->includes, []);
    }

    public function constructWithFinderConfigSrc(): void
    {
        // Arrange
        $finder = new FinderConfig([__DIR__]);

        // Act
        $config = new ApplicationConfig(src: $finder);

        // Assert
        Assert::same($config->src, $finder);
    }

    public function constructWithArraySrcNormalizesToFinderConfig(): void
    {
        // Act
        $config = new ApplicationConfig(src: [__DIR__]);

        // Assert
        Assert::instanceOf($config->src, FinderConfig::class);
        Assert::same(\count($config->src->includes), 1);
    }

    public function constructWithDefaultSuites(): void
    {
        // Act
        $config = new ApplicationConfig();

        // Assert
        Assert::same(\count($config->suites), 1);
        Assert::same($config->suites[0]->name, 'default');
    }

    public function constructWithCustomSuites(): void
    {
        // Arrange
        $suite = new SuiteConfig(name: 'custom', location: [__DIR__]);

        // Act
        $config = new ApplicationConfig(suites: [$suite]);

        // Assert
        Assert::same(\count($config->suites), 1);
        Assert::same($config->suites[0]->name, 'custom');
    }

    #[ExpectException(\InvalidArgumentException::class)]
    public function constructWithEmptySuitesThrowsException(): void
    {
        new ApplicationConfig(suites: []);
    }

    #[ExpectException(\InvalidArgumentException::class)]
    public function constructWithInvalidSuiteThrowsException(): void
    {
        /** @phpstan-ignore-next-line */
        new ApplicationConfig(suites: ['not-a-suite']);
    }

    public function constructWithDefaultPluginsAreEmpty(): void
    {
        // Act
        $config = new ApplicationConfig();

        // Assert
        Assert::same($config->plugins, []);
    }

    public function constructWithCustomPlugins(): void
    {
        // Arrange
        $plugin = new StubPlugin();

        // Act
        $config = new ApplicationConfig(plugins: [$plugin]);

        // Assert
        Assert::same($config->plugins, [$plugin]);
    }
}
