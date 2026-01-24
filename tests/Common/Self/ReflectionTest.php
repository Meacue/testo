<?php

declare(strict_types=1);

namespace Tests\Common\Self;

use Testo\Assert;
use Testo\Common\Reflection;
use Tests\Common\Stub\AnotherMarkerAttribute;
use Tests\Common\Stub\BaseClass;
use Tests\Common\Stub\CallStack\CallStackAttribute;
use Tests\Common\Stub\CallStack\CallStackBaseClass;
use Tests\Common\Stub\CallStack\CallStackChildClass;
use Tests\Common\Stub\CallStack\CallStackTestClass;
use Tests\Common\Stub\ChildClass;
use Tests\Common\Stub\ClassWithoutMarkedMethods;
use Tests\Common\Stub\MarkerAttribute;
use Tests\Common\Stub\MiddleClass;

use function Tests\Common\Stub\CallStack\nestedFunction;
use function Tests\Common\Stub\CallStack\topLevelFunction;
use function Tests\Common\Stub\CallStack\unmarkedFunction;

require_once __DIR__ . '/../Stub/CallStack/CallStackHelpers.php';

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

function testGetAttributesFromCallStackWithFunction(): void
{
    $attributes = topLevelFunction(CallStackAttribute::class);

    Assert::same(1, \count($attributes));
    Assert::same(CallStackAttribute::class, $attributes[0]->getName());
    $instance = $attributes[0]->newInstance();
    Assert::same('topFunction', $instance->label);
}

function testGetAttributesFromCallStackWithNestedFunctions(): void
{
    $attributes = nestedFunction(CallStackAttribute::class);

    Assert::same(2, \count($attributes));

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    Assert::true(\in_array('topFunction', $labels, true));
    Assert::true(\in_array('nestedFunction', $labels, true));
}

function testGetAttributesFromCallStackWithMethod(): void
{
    $obj = new CallStackTestClass();
    $attributes = $obj->methodA(CallStackAttribute::class);

    Assert::same(1, \count($attributes));
    Assert::same('methodA', $attributes[0]->newInstance()->label);
}

function testGetAttributesFromCallStackWithNestedMethods(): void
{
    $obj = new CallStackTestClass();
    $attributes = $obj->methodB(CallStackAttribute::class);

    Assert::same(2, \count($attributes));

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    Assert::true(\in_array('methodA', $labels, true));
    Assert::true(\in_array('methodB', $labels, true));
}

function testGetAttributesFromCallStackWithInheritance(): void
{
    $obj = new CallStackChildClass();
    $attributes = $obj->baseMethod(CallStackAttribute::class);

    Assert::same(1, \count($attributes));
    Assert::same('baseMethod', $attributes[0]->newInstance()->label);
}

function testGetAttributesFromCallStackFindsPrototypeAttributes(): void
{
    $obj = new CallStackChildClass();
    $attributes = $obj->childMethod(CallStackAttribute::class);

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    // Should find: childMethod (direct), overridden (from prototype in base class)
    Assert::true(\in_array('childMethod', $labels, true));
    Assert::true(\in_array('overridden', $labels, true));
}

function testGetAttributesFromCallStackWithoutPrototypes(): void
{
    $obj = new CallStackChildClass();
    $attributes = $obj->childMethod(CallStackAttribute::class, false);

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    // Should find only childMethod (direct attribute), not overridden (from prototype)
    Assert::true(\in_array('childMethod', $labels, true));
    Assert::false(\in_array('overridden', $labels, true));
}

function testGetAttributesFromCallStackReturnsEmptyWhenNoAttributes(): void
{
    $attributes = unmarkedFunction(CallStackAttribute::class);

    Assert::same([], $attributes);
}

function testGetAttributesFromCallStackReturnsEmptyWhenNoMatching(): void
{
    $obj = new CallStackTestClass();
    $attributes = $obj->methodA(MarkerAttribute::class);

    Assert::same([], $attributes);
}

function testGetAttributesFromCallStackWithNullAttributeClass(): void
{
    $attributes = topLevelFunction(null);

    // Should return all attributes, not just CallStackAttribute
    Assert::true(\count($attributes) >= 1);
}

function testGetAttributesFromCallStackIncludesClassAttributes(): void
{
    $obj = new CallStackTestClass();
    $attributes = $obj->methodA(CallStackAttribute::class, true, true);

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    // Should find both method attribute and class attribute
    Assert::true(\in_array('methodA', $labels, true));
    Assert::true(\in_array('classAttribute', $labels, true));
}

function testGetAttributesFromCallStackWithoutClassAttributes(): void
{
    $obj = new CallStackTestClass();
    $attributes = $obj->methodA(CallStackAttribute::class, true, false);

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    // Should find only method attribute, not class attribute
    Assert::true(\in_array('methodA', $labels, true));
    Assert::false(\in_array('classAttribute', $labels, true));
}

function testGetAttributesFromCallStackIncludesParentClassAttributes(): void
{
    $obj = new CallStackChildClass();
    $attributes = $obj->childMethod(CallStackAttribute::class, true, true, true);

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    // Should find child class and parent class attributes
    Assert::true(\in_array('childClassAttribute', $labels, true));
    Assert::true(\in_array('baseClassAttribute', $labels, true));
}

function testGetAttributesFromCallStackWithoutParentClassAttributes(): void
{
    $obj = new CallStackChildClass();
    $attributes = $obj->childMethod(CallStackAttribute::class, true, true, false);

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    // Should find only child class attribute, not parent
    Assert::true(\in_array('childClassAttribute', $labels, true));
    Assert::false(\in_array('baseClassAttribute', $labels, true));
}

function testGetAttributesFromCallStackWithLimit(): void
{
    $obj = new CallStackTestClass();
    $attributes = $obj->methodB(CallStackAttribute::class, true, false, true, true, 1);

    // Should return only 1 attribute due to limit
    Assert::same(1, \count($attributes));
}

function testGetAttributesFromCallStackWithLimitGreaterThanResults(): void
{
    $attributes = topLevelFunction(CallStackAttribute::class, true, false, true, true, 100);

    // Should return all available attributes (less than limit)
    Assert::same(1, \count($attributes));
}

function testGetAttributesFromCallStackWithLimitAndNestedCalls(): void
{
    $obj = new CallStackTestClass();
    $attributes = $obj->methodB(CallStackAttribute::class, true, false, true, true, 2);

    // Should return exactly 2 attributes
    Assert::same(2, \count($attributes));

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    // Should find the first two attributes from the call stack
    Assert::true(\in_array('methodA', $labels, true));
    Assert::true(\in_array('methodB', $labels, true));
}

function testGetAttributesFromCallStackDuplicatesClassAttributesFromHierarchy(): void
{
    $obj = new CallStackChildClass();
    // Call stack: childMethod() -> overriddenMethod()
    // With includeClasses=true and includeParents=true:
    // - overriddenMethod scans CallStackChildClass + CallStackBaseClass
    // - childMethod scans CallStackChildClass + CallStackBaseClass
    // Result: childClassAttribute appears twice, baseClassAttribute appears twice
    $attributes = $obj->childMethod(CallStackAttribute::class, true, true, true);

    $labels = \array_map(
        static fn(\ReflectionAttribute $attr) => $attr->newInstance()->label,
        $attributes,
    );

    // Count occurrences of each label
    $childClassCount = \count(\array_filter($labels, static fn($l) => $l === 'childClassAttribute'));
    $baseClassCount = \count(\array_filter($labels, static fn($l) => $l === 'baseClassAttribute'));

    // Both class attributes should appear twice (once per method in call stack)
    Assert::same(2, $childClassCount);
    Assert::same(2, $baseClassCount);
}
