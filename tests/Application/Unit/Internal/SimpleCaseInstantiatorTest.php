<?php

declare(strict_types=1);

namespace Tests\Application\Unit\Internal;

use Testo\Application\Exception\TestCaseInstantiationException;
use Testo\Application\Internal\SimpleCaseInstantiator;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;
use Tests\Fixture\Runner\InstantiableClass;
use Tests\Fixture\Runner\NonInstantiableClass;

#[Test]
#[Covers(SimpleCaseInstantiator::class)]
final class SimpleCaseInstantiatorTest
{
    public function hasInstanceIsFalseBeforeFirstGet(): void
    {
        $instantiator = new SimpleCaseInstantiator(new \ReflectionClass(InstantiableClass::class));

        Assert::false($instantiator->hasInstance());
    }

    public function getInstanceCreatesObjectOfReflectedClass(): void
    {
        $instantiator = new SimpleCaseInstantiator(new \ReflectionClass(InstantiableClass::class));

        $instance = $instantiator->getInstance();

        Assert::instanceOf($instance, InstantiableClass::class);
    }

    public function hasInstanceBecomesTrueAfterFirstGet(): void
    {
        $instantiator = new SimpleCaseInstantiator(new \ReflectionClass(InstantiableClass::class));
        $instantiator->getInstance();

        Assert::true($instantiator->hasInstance());
    }

    public function getInstanceReturnsCachedObjectOnRepeatedCalls(): void
    {
        $instantiator = new SimpleCaseInstantiator(new \ReflectionClass(InstantiableClass::class));

        $first = $instantiator->getInstance();
        $second = $instantiator->getInstance();

        Assert::same($second, $first);
    }

    public function getInstanceWrapsReflectionFailureIntoTestCaseInstantiationException(): never
    {
        $instantiator = new SimpleCaseInstantiator(new \ReflectionClass(NonInstantiableClass::class));

        Expect::exception(TestCaseInstantiationException::class);

        $instantiator->getInstance();
    }
}
