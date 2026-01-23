<?php

declare(strict_types=1);

namespace Tests\Common\Self;

use Testo\Assert;
use Testo\Common\Reflection;
use Tests\Common\Stub\AnotherMarkerAttribute;
use Tests\Common\Stub\BaseClass;
use Tests\Common\Stub\ChildClass;
use Tests\Common\Stub\ClassWithoutMarkedMethods;
use Tests\Common\Stub\MarkerAttribute;
use Tests\Common\Stub\MiddleClass;

function testFindsDirectlyMarkedMethods(): void
{
    $methods = Reflection::findMethodsWithAttribute(BaseClass::class, MarkerAttribute::class);

    $names = \array_map(static fn(\ReflectionMethod $m) => $m->getName(), $methods);
    \sort($names);

    Assert::same(['baseMethod', 'overriddenMethod'], $names);
}

function testFindsInheritedMarkedMethods(): void
{
    $methods = Reflection::findMethodsWithAttribute(MiddleClass::class, MarkerAttribute::class);

    $names = \array_map(static fn(\ReflectionMethod $m) => $m->getName(), $methods);
    \sort($names);

    Assert::same(['baseMethod', 'overriddenMethod'], $names);
}

function testFindsMethodsMarkedInPrototype(): void
{
    $methods = Reflection::findMethodsWithAttribute(ChildClass::class, MarkerAttribute::class);

    $names = \array_map(static fn(\ReflectionMethod $m) => $m->getName(), $methods);
    \sort($names);

    Assert::same(['baseMethod', 'childMethod', 'interfaceMethod', 'overriddenMethod'], $names);
}

function testFindsMethodsFromInterface(): void
{
    $methods = Reflection::findMethodsWithAttribute(ChildClass::class, MarkerAttribute::class);

    $names = \array_map(static fn(\ReflectionMethod $m) => $m->getName(), $methods);

    Assert::true(\in_array('interfaceMethod', $names, true));
}

function testWithoutPrototypesSkipsPrototypeSearch(): void
{
    $methods = Reflection::findMethodsWithAttribute(
        ChildClass::class,
        MarkerAttribute::class,
        includePrototypes: false,
    );

    $names = \array_map(static fn(\ReflectionMethod $m) => $m->getName(), $methods);
    \sort($names);

    // baseMethod has attribute directly, childMethod has attribute directly
    // interfaceMethod and overriddenMethod only have attributes in prototypes
    Assert::same(['baseMethod', 'childMethod'], $names);
}

function testReturnsEmptyArrayWhenNoMatches(): void
{
    $methods = Reflection::findMethodsWithAttribute(ClassWithoutMarkedMethods::class, MarkerAttribute::class);

    Assert::same([], $methods);
}

function testFiltersByAttributeClass(): void
{
    $methods = Reflection::findMethodsWithAttribute(ChildClass::class, AnotherMarkerAttribute::class);

    $names = \array_map(static fn(\ReflectionMethod $m) => $m->getName(), $methods);

    Assert::same(['middleMethod'], $names);
}

function testAcceptsReflectionClassInstance(): void
{
    $ref = new \ReflectionClass(BaseClass::class);
    $methods = Reflection::findMethodsWithAttribute($ref, MarkerAttribute::class);

    $names = \array_map(static fn(\ReflectionMethod $m) => $m->getName(), $methods);
    \sort($names);

    Assert::same(['baseMethod', 'overriddenMethod'], $names);
}
