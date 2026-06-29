<?php

declare(strict_types=1);

namespace Tests\Core\Pipeline\Attribute;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Policy\ConflictPolicy;
use Testo\Test;

#[Test]
#[Covers(InterceptorOptions::class)]
final class InterceptorOptionsTest
{
    public function constructorWithDefaultValues(): void
    {
        $options = new InterceptorOptions();

        Assert::same(0, InterceptorOptions::ORDER_DEFAULT);
        Assert::same(InterceptorOptions::ORDER_DEFAULT, $options->order);
        Assert::same(ConflictPolicy::First, $options->onConflict);
        Assert::same([], $options->testType);
    }

    public function resolvableAsClassAttribute(): void
    {
        $attributes = (new \ReflectionClass(InterceptorOptionsTestFixture::class))
            ->getAttributes(InterceptorOptions::class);

        Assert::count($attributes, 1);

        $options = $attributes[0]->newInstance();

        Assert::instanceOf($options, InterceptorOptions::class);
        Assert::same(InterceptorOptions::ORDER_ASSERTIONS, $options->order);
        Assert::same(ConflictPolicy::Merge, $options->onConflict);
        Assert::same(['unit', 'integration'], $options->testType);
    }
}

#[InterceptorOptions(
    order: InterceptorOptions::ORDER_ASSERTIONS,
    onConflict: ConflictPolicy::Merge,
    testType: ['unit', 'integration'],
)]
final class InterceptorOptionsTestFixture {}
