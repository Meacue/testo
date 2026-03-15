<?php

declare(strict_types=1);

namespace Tests\Application\Unit\Config;

use Testo\Application\Config\Plugin\PluginCollection;
use Testo\Application\Config\Plugin\SuitePlugins;
use Testo\Assert;
use Testo\Assert\ExpectException;
use Testo\Test;
use Tests\Application\Stub\StubPlugin;

#[Test]
final class PluginCollectionTest
{
    public function defaultsReturnsNonEmptyCollection(): void
    {
        Assert::true(\count(SuitePlugins::defaults()) > 0);
    }

    public function emptyCollectionIsEmpty(): void
    {
        Assert::same((new PluginCollection())->toArray(), []);
    }

    public function withAddsPluginToDefaults(): void
    {
        // Arrange
        $plugin = new StubPlugin();
        $defaultCount = \count(SuitePlugins::defaults());

        // Act
        $result = SuitePlugins::with($plugin)->toArray();

        // Assert: defaults + stub
        Assert::count($result, $defaultCount + 1);
        Assert::same($result[\count($result) - 1], $plugin);
    }

    public function withMultiplePlugins(): void
    {
        // Arrange
        $a = new StubPlugin('a');
        $b = new StubPlugin('b');
        $defaultCount = \count(SuitePlugins::defaults());

        // Act
        $result = SuitePlugins::with($a, $b)->toArray();

        // Assert
        Assert::count($result, $defaultCount + 2);
        Assert::same($result[\count($result) - 2], $a);
        Assert::same($result[\count($result) - 1], $b);
    }

    public function withoutRemovesPlugin(): void
    {
        // Arrange
        $defaults = SuitePlugins::defaults()->toArray();
        $targetClass = $defaults[0]::class;

        // Act
        $result = SuitePlugins::with()->without($targetClass)->toArray();

        // Assert
        foreach ($result as $p) {
            Assert::notSame($p::class, $targetClass);
        }
        Assert::count($result, \count($defaults) - 1);
    }

    public function onlyReplacesDefaults(): void
    {
        // Arrange
        $plugin = new StubPlugin();

        // Act
        $result = SuitePlugins::only($plugin)->toArray();

        // Assert
        Assert::count($result, 1);
        Assert::same($result[0], $plugin);
    }

    public function onlyWithNoPluginsReturnsEmpty(): void
    {
        Assert::same(SuitePlugins::only()->toArray(), []);
    }

    public function chainingWithThenWithout(): void
    {
        // Arrange
        $plugin = new StubPlugin();
        $defaultClasses = \array_map(static fn(object $p) => $p::class, SuitePlugins::defaults()->toArray());

        // Act
        $collection = SuitePlugins::with($plugin);
        foreach ($defaultClasses as $class) {
            $collection = $collection->without($class);
        }
        $result = $collection->toArray();

        // Assert: only the custom plugin
        Assert::count($result, 1);
        Assert::same($result[0], $plugin);
    }

    public function chainingWithoutThenWith(): void
    {
        // Arrange
        $plugin = new StubPlugin();
        $defaultClasses = \array_map(static fn(object $p) => $p::class, SuitePlugins::defaults()->toArray());

        // Act
        $collection = SuitePlugins::with();
        foreach ($defaultClasses as $class) {
            $collection = $collection->without($class);
        }
        $result = $collection->with($plugin)->toArray();

        // Assert
        Assert::count($result, 1);
        Assert::same($result[0], $plugin);
    }

    public function longChain(): void
    {
        // Arrange
        $a = new StubPlugin('a');
        $b = new StubPlugin('b');
        $c = new StubPlugin('c');
        $defaultClasses = \array_map(static fn(object $p) => $p::class, SuitePlugins::defaults()->toArray());

        // Act
        $collection = SuitePlugins::with($a);
        foreach ($defaultClasses as $class) {
            $collection = $collection->without($class);
        }
        $result = $collection
            ->with($b)
            ->without(StubPlugin::class)
            ->with($c)
            ->toArray();

        // Assert: all StubPlugins before last with() removed, only $c remains
        Assert::count($result, 1);
        Assert::same($result[0], $c);
    }

    public function onlyThenWithChain(): void
    {
        // Arrange
        $a = new StubPlugin('a');
        $b = new StubPlugin('b');

        // Act
        $result = SuitePlugins::only($a)->with($b)->toArray();

        // Assert
        Assert::count($result, 2);
        Assert::same($result[0], $a);
        Assert::same($result[1], $b);
    }

    #[ExpectException(\TypeError::class)]
    public function validationRejectsNonPlugin(): void
    {
        /** @phpstan-ignore-next-line */
        new PluginCollection('not-a-plugin');
    }

    public function withoutNonExistentClassHasNoEffect(): void
    {
        // Arrange
        $defaultCount = \count(SuitePlugins::defaults());

        // Act
        $result = SuitePlugins::with()->without(StubPlugin::class)->toArray();

        // Assert
        Assert::count($result, $defaultCount);
    }

    public function orderDefaultsBeforeCustomPlugins(): void
    {
        // Arrange
        $plugin = new StubPlugin();
        $defaults = SuitePlugins::defaults()->toArray();

        // Act
        $result = SuitePlugins::with($plugin)->toArray();

        // Assert: defaults come first, custom last
        Assert::same($result[0]::class, $defaults[0]::class);
        Assert::same($result[\count($result) - 1], $plugin);
    }

    public function countReturnsCollectionSize(): void
    {
        $defaultCount = \count(SuitePlugins::defaults());

        Assert::count(new PluginCollection(), 0);
        Assert::count(SuitePlugins::with(new StubPlugin()), $defaultCount + 1);
        Assert::count(SuitePlugins::only(), 0);
    }

    public function iterableYieldsPlugins(): void
    {
        // Arrange
        $collection = SuitePlugins::with(new StubPlugin());

        // Act
        $items = [];
        foreach ($collection as $item) {
            $items[] = $item;
        }

        // Assert
        Assert::same($items, $collection->toArray());
    }

    public function collectionWithIsImmutable(): void
    {
        // Arrange
        $defaultCount = \count(SuitePlugins::defaults());
        $original = SuitePlugins::with(new StubPlugin('a'));

        // Act
        $modified = $original->with(new StubPlugin('b'));

        // Assert
        Assert::count($original, $defaultCount + 1);
        Assert::count($modified, $defaultCount + 2);
    }

    public function collectionWithoutIsImmutable(): void
    {
        // Arrange
        $defaults = SuitePlugins::defaults()->toArray();
        $original = SuitePlugins::with();
        $targetClass = $defaults[0]::class;

        // Act
        $modified = $original->without($targetClass);

        // Assert
        Assert::count($original, \count($defaults));
        Assert::count($modified, \count($defaults) - 1);
    }
}
