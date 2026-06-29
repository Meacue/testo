<?php

declare(strict_types=1);

namespace Tests\Core\Pipeline;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Pipeline\Interceptor;
use Testo\Pipeline\InterceptorProvider;
use Testo\Pipeline\Internal\AttributesInterceptor;
use Testo\Test;
use Internal\Container\ObjectContainer;

#[Test]
#[Covers(InterceptorProvider::class)]
final class InterceptorProviderTest
{
    public function addInterceptorResolvesStringClassNameViaContainer(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);

        $provider->addInterceptor(TestInterceptorForProvider::class);
        $result = $provider->fromConfig(TestInterceptorForProvider::class);

        Assert::same(\count($result), 1);
        Assert::true($result[0] instanceof TestInterceptorForProvider);
    }

    public function addInterceptorKeepsInterceptorInstanceIdentity(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $interceptor = new TestInterceptorForProvider();

        $provider->addInterceptor($interceptor);
        $result = $provider->fromConfig(TestInterceptorForProvider::class);

        Assert::same(\count($result), 1);
        Assert::same($result[0], $interceptor);
    }

    /** The provider is pre-seeded with {@see AttributesInterceptor}; nothing else matches the filter here. */
    public function fromConfigReturnsEmptyArrayWhenOnlySeedIsPresent(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);

        $result = $provider->fromConfig(TestInterceptorForProvider::class);

        Assert::same($result, []);
    }

    public function fromConfigResolvesPreSeededAttributesInterceptor(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);

        $result = $provider->fromConfig(AttributesInterceptor::class);

        Assert::same(\count($result), 1);
        Assert::true($result[0] instanceof AttributesInterceptor);
    }

    public function fromConfigReturnsMatchingInterceptors(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $interceptor = new TestInterceptorForProvider();

        $provider->addInterceptor($interceptor);
        $result = $provider->fromConfig(TestInterceptorForProvider::class);

        Assert::same(\count($result), 1);
        Assert::same($result[0], $interceptor);
    }

    public function fromConfigFiltersInterceptorsByClass(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $testInterceptor = new TestInterceptorForProvider();
        $otherInterceptor = new AnotherTestInterceptor();

        $provider->addInterceptor($testInterceptor);
        $provider->addInterceptor($otherInterceptor);

        $result = $provider->fromConfig(TestInterceptorForProvider::class);

        Assert::same(\count($result), 1);
        Assert::same($result[0], $testInterceptor);
    }

    public function fromClassesReturnsEmptyArrayWhenNoInterceptorsProvided(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);

        $result = $provider->fromClasses(TestInterceptorForProvider::class);

        Assert::same($result, []);
    }

    public function fromClassesFiltersByInstanceType(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $testInterceptor = new TestInterceptorForProvider();

        $result = $provider->fromClasses(TestInterceptorForProvider::class, $testInterceptor);

        Assert::same(\count($result), 1);
        Assert::same($result[0], $testInterceptor);
    }

    public function fromClassesFiltersOutNonMatchingInstances(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $testInterceptor = new TestInterceptorForProvider();
        $otherInterceptor = new AnotherTestInterceptor();

        $result = $provider->fromClasses(TestInterceptorForProvider::class, $testInterceptor, $otherInterceptor);

        Assert::same(\count($result), 1);
        Assert::same($result[0], $testInterceptor);
    }

    public function fromClassesInstantiatesClassStringWhenClassExists(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);

        $result = $provider->fromClasses(TestInterceptorForProvider::class, TestInterceptorForProvider::class);

        Assert::same(\count($result), 1);
        Assert::true($result[0] instanceof TestInterceptorForProvider);
    }

    public function fromClassesSkipsClassStringWhenClassDoesNotExist(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);

        $result = $provider->fromClasses(TestInterceptorForProvider::class, 'NonExistentClass');

        Assert::same($result, []);
    }

    public function fromClassesSkipsClassStringWhenNotSubclassOfTargetClass(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);

        $result = $provider->fromClasses(TestInterceptorForProvider::class, AnotherTestInterceptor::class);

        Assert::same($result, []);
    }

    public function fromClassesMixesClassStringsAndInstances(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $instance = new TestInterceptorForProvider();

        $result = $provider->fromClasses(
            TestInterceptorForProvider::class,
            TestInterceptorForProvider::class,
            $instance,
        );

        Assert::same(\count($result), 2);
        Assert::true($result[0] instanceof TestInterceptorForProvider);
        Assert::same($result[1], $instance);
    }

    public function fromAttributesReturnsEmptyArrayWhenNoAttributesProvided(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);

        $result = $provider->fromAttributes(TestInterceptorForProvider::class);

        Assert::same($result, []);
    }

    public function fromAttributesThrowsWhenAttributeHasNoInterceptor(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $attribute = new TestAttributeWithoutInterceptor();

        try {
            $provider->fromAttributes(TestInterceptorForProvider::class, $attribute);
            Assert::fail('Should have thrown RuntimeException');
        } catch (\RuntimeException $e) {
            Assert::true(\str_contains($e->getMessage(), 'No interceptor found for attribute'));
        }
    }

    public function fromAttributesReturnsInterceptorForAttribute(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $attribute = new TestAttributeWithInterceptor();

        $result = $provider->fromAttributes(TestInterceptorForProvider::class, $attribute);

        Assert::same(\count($result), 1);
        Assert::true($result[0] instanceof TestInterceptorForProvider);
    }

    public function fromAttributesFiltersInterceptorsByClass(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $attribute = new TestAttributeWithInterceptor();

        $result = $provider->fromAttributes(AnotherTestInterceptor::class, $attribute);

        Assert::same($result, []);
    }

    public function fromAttributesCreatesMultipleInterceptorsForMultipleAttributes(): void
    {
        $container = new ObjectContainer();
        $provider = new InterceptorProvider($container);
        $attr1 = new TestAttributeWithInterceptor();
        $attr2 = new TestAttributeWithInterceptor();

        $result = $provider->fromAttributes(TestInterceptorForProvider::class, $attr1, $attr2);

        Assert::same(\count($result), 2);
        Assert::true($result[0] instanceof TestInterceptorForProvider);
        Assert::true($result[1] instanceof TestInterceptorForProvider);
    }
}

final class TestInterceptorForProvider implements Interceptor {}

final class AnotherTestInterceptor implements Interceptor {}

#[\Attribute(\Attribute::TARGET_CLASS)]
#[FallbackInterceptor(TestInterceptorForProvider::class)]
final class TestAttributeWithInterceptor implements Interceptable {}

#[\Attribute(\Attribute::TARGET_CLASS)]
final class TestAttributeWithoutInterceptor implements Interceptable {}
