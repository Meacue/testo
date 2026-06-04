<?php

declare(strict_types=1);

namespace Testo\Testing\Internal;

use Internal\Container\Container;
use Testo\Testing\Attribute\Inject;

/**
 * Populates the properties of a test case instance that are marked with {@see Inject}
 * by resolving their declared type from the DI container.
 *
 * @internal
 * @psalm-internal Testo\Testing
 */
final readonly class PropertyInjector
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Autowire every {@see Inject} property of the given instance from the container.
     */
    public function inject(object $instance): void
    {
        # Walk the whole hierarchy so private properties declared on parent classes are reached too.
        for ($class = new \ReflectionClass($instance); $class !== false; $class = $class->getParentClass()) {
            foreach ($class->getProperties() as $property) {
                # Each level handles only the properties it declares to avoid touching the same
                # inherited property twice while still reaching private parent properties.
                if ($property->getDeclaringClass()->getName() !== $class->getName()) {
                    continue;
                }

                if ($property->getAttributes(Inject::class) === []) {
                    continue;
                }

                $property->setValue($instance, $this->resolve($property));
            }
        }
    }

    private function resolve(\ReflectionProperty $property): object
    {
        $type = $property->getType();
        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            throw new \LogicException(\sprintf(
                'Property %s::$%s marked with #[%s] must declare a single class-typed dependency.',
                $property->getDeclaringClass()->getName(),
                $property->getName(),
                Inject::class,
            ));
        }

        /** @var class-string $id */
        $id = $type->getName();
        return $this->container->get($id);
    }
}
