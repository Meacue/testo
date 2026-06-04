<?php

declare(strict_types=1);

namespace Tests\Core\Testing\Unit;

use Internal\Container\ObjectContainer;
use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;
use Testo\Testing\Internal\PropertyInjector;
use Tests\Core\Testing\Stub\InjectChild;
use Tests\Core\Testing\Stub\InjectService;
use Tests\Core\Testing\Stub\InjectTarget;
use Tests\Core\Testing\Stub\InvalidInjectTarget;

#[Test]
#[Covers(PropertyInjector::class)]
final class PropertyInjectorTest
{
    public function injectsClassTypedProperty(): void
    {
        $service = new InjectService();
        $target = new InjectTarget();

        $this->injectorFor($service)->inject($target);

        Assert::same($target->service, $service);
    }

    public function leavesPropertiesWithoutInjectAttributeUntouched(): void
    {
        $target = new InjectTarget();

        $this->injectorFor(new InjectService())->inject($target);

        Assert::false((new \ReflectionProperty($target, 'untouched'))->isInitialized($target));
    }

    public function reachesPrivatePropertyDeclaredOnParent(): void
    {
        $service = new InjectService();
        $target = new InjectChild();

        $this->injectorFor($service)->inject($target);

        Assert::same($target->parentService(), $service);
        Assert::same($target->childService, $service);
    }

    public function throwsWhenInjectPropertyIsNotClassTyped(): never
    {
        Expect::exception(\LogicException::class);

        $this->injectorFor(new InjectService())->inject(new InvalidInjectTarget());
    }

    private function injectorFor(InjectService $service): PropertyInjector
    {
        $container = new ObjectContainer();
        $container->set($service);

        return new PropertyInjector($container);
    }
}
