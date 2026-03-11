<?php

declare(strict_types=1);

namespace Tests\Application\Unit\Config;

use Testo\Application\Config\Plugin\PluginCollection;
use Testo\Application\Config\Plugin\SuitePlugins;
use Testo\Assert;
use Testo\Assert\ExpectException;
use Testo\Attribute\Test;
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
        Assert::same([], (new PluginCollection())->toArray());
    }

    public function withAddsPluginToDefaults(): void
    {
        // Arrange
        $plugin = new StubPlugin();
        $defaultCount = \count(SuitePlugins::defaults());

        // Act
        $result = SuitePlugins::with($plugin)->toArray();

        // Assert: defaults + stub
        Assert::same($defaultCount + 1, \count($result));
        Assert::same($plugin, $result[\count($result) - 1]);
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
        Assert::same($defaultCount + 2, \count($result));
        Assert::same($a, $result[\count($result) - 2]);
        Assert::same($b, $result[\count($result) - 1]);
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
            Assert::notSame($targetClass, $p::class);
        }
        Assert::same(\count($defaults) - 1, \count($result));
    }

    public function onlyReplacesDefaults(): void
    {
        // Arrange
        $plugin = new StubPlugin();

        // Act
        $result = SuitePlugins::only($plugin)->toArray();

        // Assert
        Assert::same(1, \count($result));
        Assert::same($plugin, $result[0]);
    }

    public function onlyWithNoPluginsReturnsEmpty(): void
    {
        Assert::same([], SuitePlugins::only()->toArray());
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
        Assert::same(1, \count($result));
        Assert::same($plugin, $result[0]);
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
        Assert::same(1, \count($result));
        Assert::same($plugin, $result[0]);
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
        Assert::same(1, \count($result));
        Assert::same($c, $result[0]);
    }

    public function onlyThenWithChain(): void
    {
        // Arrange
        $a = new StubPlugin('a');
        $b = new StubPlugin('b');

        // Act
        $result = SuitePlugins::only($a)->with($b)->toArray();

        // Assert
        Assert::same(2, \count($result));
        Assert::same($a, $result[0]);
        Assert::same($b, $result[1]);
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
        Assert::same($defaultCount, \count($result));
    }

    public function orderDefaultsBeforeCustomPlugins(): void
    {
        // Arrange
        $plugin = new StubPlugin();
        $defaults = SuitePlugins::defaults()->toArray();

        // Act
        $result = SuitePlugins::with($plugin)->toArray();

        // Assert: defaults come first, custom last
        Assert::same($defaults[0]::class, $result[0]::class);
        Assert::same($plugin, $result[\count($result) - 1]);
    }

    public function countReturnsCollectionSize(): void
    {
        $defaultCount = \count(SuitePlugins::defaults());

        Assert::same(0, \count(new PluginCollection()));
        Assert::same($defaultCount + 1, \count(SuitePlugins::with(new StubPlugin())));
        Assert::same(0, \count(SuitePlugins::only()));
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
        Assert::same($collection->toArray(), $items);
    }

    public function collectionWithIsImmutable(): void
    {
        // Arrange
        $defaultCount = \count(SuitePlugins::defaults());
        $original = SuitePlugins::with(new StubPlugin('a'));

        // Act
        $modified = $original->with(new StubPlugin('b'));

        // Assert
        Assert::same($defaultCount + 1, \count($original));
        Assert::same($defaultCount + 2, \count($modified));
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
        Assert::same(\count($defaults), \count($original));
        Assert::same(\count($defaults) - 1, \count($modified));
    }
}
