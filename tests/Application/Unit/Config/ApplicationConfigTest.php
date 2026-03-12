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
        Assert::instanceOf(FinderConfig::class, $config->src);
        Assert::same([], $config->src->includes);
    }

    public function constructWithFinderConfigSrc(): void
    {
        // Arrange
        $finder = new FinderConfig([__DIR__]);

        // Act
        $config = new ApplicationConfig(src: $finder);

        // Assert
        Assert::same($finder, $config->src);
    }

    public function constructWithArraySrcNormalizesToFinderConfig(): void
    {
        // Act
        $config = new ApplicationConfig(src: [__DIR__]);

        // Assert
        Assert::instanceOf(FinderConfig::class, $config->src);
        Assert::same(1, \count($config->src->includes));
    }

    public function constructWithDefaultSuites(): void
    {
        // Act
        $config = new ApplicationConfig();

        // Assert
        Assert::same(1, \count($config->suites));
        Assert::same('default', $config->suites[0]->name);
    }

    public function constructWithCustomSuites(): void
    {
        // Arrange
        $suite = new SuiteConfig(name: 'custom', location: [__DIR__]);

        // Act
        $config = new ApplicationConfig(suites: [$suite]);

        // Assert
        Assert::same(1, \count($config->suites));
        Assert::same('custom', $config->suites[0]->name);
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
        Assert::same([], $config->plugins);
    }

    public function constructWithCustomPlugins(): void
    {
        // Arrange
        $plugin = new StubPlugin();

        // Act
        $config = new ApplicationConfig(plugins: [$plugin]);

        // Assert
        Assert::same([$plugin], $config->plugins);
    }
}
