<?php

declare(strict_types=1);

namespace Tests\Core\Pipeline;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Pipeline\Internal\Cache;
use Testo\Pipeline\Interceptor;
use Testo\Test;

#[Test]
#[Covers(Cache::class)]
final class CacheTest
{
    /**
     * When resolveAlias is called with a class that has a FallbackInterceptor attribute,
     * it should cache and return the interceptor class.
     */
    public function resolveAliasWithFallbackInterceptorAttribute(): void
    {
        $result = Cache::resolveAlias(AttributeWithFallback::class);

        Assert::same(MockInterceptor::class, $result);
    }

    /**
     * When resolveAlias is called with a class that has no FallbackInterceptor attribute,
     * it should return null.
     */
    public function resolveAliasWithoutFallbackInterceptorAttribute(): void
    {
        $result = Cache::resolveAlias(AttributeWithoutFallback::class);

        Assert::null($result);
    }

    /**
     * The first resolveAlias call memoises the resolved value in the private static map,
     * so the key must be present (with the resolved value) afterwards.
     */
    public function resolveAliasMemoisesResultInMap(): void
    {
        $map = self::mapProperty();
        $orig = $map->getValue();

        try {
            $map->setValue(null, []);

            $result = Cache::resolveAlias(AttributeWithFallbackForCache::class);
            Assert::same(MockInterceptor::class, $result);

            $stored = $map->getValue();
            Assert::true(\array_key_exists(AttributeWithFallbackForCache::class, $stored));
            Assert::same(MockInterceptor::class, $stored[AttributeWithFallbackForCache::class]);
        } finally {
            $map->setValue(null, $orig);
        }
    }

    /**
     * Once a parent class is memoised in the map, resolving a child class walks up the
     * cached map (the do/while loop) and returns the parent's stored value.
     */
    public function resolveAliasWalksCachedParentInMap(): void
    {
        $map = self::mapProperty();
        $orig = $map->getValue();

        try {
            $map->setValue(null, []);

            $parent = Cache::resolveAlias(ParentAttributeForMapWalk::class);
            Assert::same(MockInterceptor::class, $parent);

            $stored = $map->getValue();
            Assert::true(\array_key_exists(ParentAttributeForMapWalk::class, $stored));
            Assert::false(\array_key_exists(ChildAttributeForMapWalk::class, $stored));

            $child = Cache::resolveAlias(ChildAttributeForMapWalk::class);
            Assert::same(MockInterceptor::class, $child);
        } finally {
            $map->setValue(null, $orig);
        }
    }

    /**
     * A class without a FallbackInterceptor caches null via `??=`; the lookup uses
     * array_key_exists, so the stored null is a cache hit on subsequent calls.
     */
    public function resolveAliasCachesNullAsHit(): void
    {
        $map = self::mapProperty();
        $orig = $map->getValue();

        try {
            $map->setValue(null, []);

            $first = Cache::resolveAlias(NoFallbackForNullCache::class);
            Assert::null($first);

            $stored = $map->getValue();
            Assert::true(\array_key_exists(NoFallbackForNullCache::class, $stored));
            Assert::null($stored[NoFallbackForNullCache::class]);

            $second = Cache::resolveAlias(NoFallbackForNullCache::class);
            Assert::null($second);
        } finally {
            $map->setValue(null, $orig);
        }
    }

    /**
     * When resolveAlias is called with a class that inherits from a class with FallbackInterceptor,
     * it should walk up the parent class chain (reflection fallback) and find the interceptor.
     */
    public function resolveAliasWalksParentClassHierarchy(): void
    {
        $result = Cache::resolveAlias(ChildAttributeOfFallback::class);

        Assert::same(MockInterceptor::class, $result);
    }

    private static function mapProperty(): \ReflectionProperty
    {
        $property = (new \ReflectionClass(Cache::class))->getProperty('map');
        $property->setAccessible(true);

        return $property;
    }
}

#[\Attribute(\Attribute::TARGET_CLASS)]
#[FallbackInterceptor(MockInterceptor::class)]
class AttributeWithFallback implements Interceptable
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AttributeWithoutFallback implements Interceptable
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
#[FallbackInterceptor(MockInterceptor::class)]
final class AttributeWithFallbackForCache implements Interceptable
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
#[FallbackInterceptor(MockInterceptor::class)]
class ParentAttributeForMapWalk implements Interceptable
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ChildAttributeForMapWalk extends ParentAttributeForMapWalk
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
final class NoFallbackForNullCache implements Interceptable
{
}

#[\Attribute(\Attribute::TARGET_CLASS)]
final class ChildAttributeOfFallback extends AttributeWithFallback
{
}

final class MockInterceptor implements Interceptor
{
}
