<?php

declare(strict_types=1);

namespace Tests\Testo\Unit\Test\Runner;

use Testo\Application\Exception\TestCaseInstantiationException;
use Testo\Application\Internal\SimpleCaseInstantiator;
use Testo\Assert;
use Testo\Expect;
use Tests\Fixture\Runner\InstantiableClass;
use Tests\Fixture\Runner\NonInstantiableClass;

final class SimpleCaseInstantiatorTest
{
    public function testHasInstanceReturnsFalseInitially(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(InstantiableClass::class);
        $instantiator = new SimpleCaseInstantiator($reflection);

        // Act
        $result = $instantiator->hasInstance();

        // Assert
        Assert::false($result);
    }

    public function testGetInstanceReturnsObject(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(InstantiableClass::class);
        $instantiator = new SimpleCaseInstantiator($reflection);

        // Act
        $instance = $instantiator->getInstance();

        // Assert
        Assert::instanceOf($instance, InstantiableClass::class);
    }

    public function testHasInstanceReturnsTrueAfterGetInstance(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(InstantiableClass::class);
        $instantiator = new SimpleCaseInstantiator($reflection);
        $instantiator->getInstance();

        // Act
        $result = $instantiator->hasInstance();

        // Assert
        Assert::true($result);
    }

    public function testGetInstanceReturnsSameInstanceOnMultipleCalls(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(InstantiableClass::class);
        $instantiator = new SimpleCaseInstantiator($reflection);

        // Act
        $first = $instantiator->getInstance();
        $second = $instantiator->getInstance();

        // Assert
        Assert::same($second, $first);
    }

    public function testGetInstanceThrowsExceptionWhenCannotInstantiate(): void
    {
        // Arrange
        $reflection = new \ReflectionClass(NonInstantiableClass::class);
        $instantiator = new SimpleCaseInstantiator($reflection);

        // Assert
        Expect::exception(TestCaseInstantiationException::class);

        // Act
        $instantiator->getInstance();
    }
}
