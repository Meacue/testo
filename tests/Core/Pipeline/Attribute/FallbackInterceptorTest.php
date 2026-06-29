<?php

declare(strict_types=1);

namespace Tests\Core\Pipeline\Attribute;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Test;

#[Test]
#[Covers(FallbackInterceptor::class)]
final class FallbackInterceptorTest
{
    public function resolvesInterceptorClassFromAnnotatedClass(): void
    {
        $attributes = (new \ReflectionClass(FallbackInterceptorTestFixture::class))
            ->getAttributes(FallbackInterceptor::class);

        Assert::count($attributes, 1);
        Assert::same(
            $attributes[0]->newInstance()->class,
            FallbackInterceptorTestInterceptor::class,
        );
    }

    public function classWithoutAttributeResolvesToNothing(): void
    {
        $attributes = (new \ReflectionClass(FallbackInterceptorTestBareFixture::class))
            ->getAttributes(FallbackInterceptor::class);

        Assert::same($attributes, []);
    }
}

#[FallbackInterceptor(FallbackInterceptorTestInterceptor::class)]
final class FallbackInterceptorTestFixture {}

final class FallbackInterceptorTestBareFixture {}

final class FallbackInterceptorTestInterceptor {}
