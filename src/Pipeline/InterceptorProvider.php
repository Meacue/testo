<?php

declare(strict_types=1);

namespace Testo\Pipeline;

use Testo\Application\Middleware\AttributesInterceptor;
use Testo\Application\Middleware\FilterInterceptor;
use Testo\Application\Middleware\Locator\FilePostfixTestLocatorInterceptor;
use Testo\Application\Middleware\Locator\TestoAttributesLocatorInterceptor;
use Testo\Assert\Interceptor\AssertCollectorInterceptor;
use Testo\Assert\Interceptor\ExpectationsInterceptor;
use Testo\Bench\Middleware\BenchFinder;
use Testo\Common\Container;
use Testo\Lifecycle\Interceptor\LifecycleInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Pipeline\Internal\Cache;
use Yiisoft\Injector\Injector;

final class InterceptorProvider implements InterceptorCollector
{
    private array $interceptors = [];
    private readonly Injector $injector;

    public function __construct(
        private readonly Container $container,
    ) {
        $this->injector = $this->container->get(Injector::class)->withCacheReflections(true);
    }

    public function addInterceptor(Interceptor|string $interceptor): void
    {
        $this->interceptors[] = $interceptor;
    }

    /**
     * Get interceptors for the given configuration filtered by the given class.
     *
     * @template-covariant T of Interceptor
     *
     * @param class-string<T> $class The target interceptor class.
     *
     * @return Interceptor Interceptor instances of the given class.
     */
    public function fromConfig(string $class): array
    {
        return $this->fromClasses($class, ...$this->interceptors, ...[
            FilterInterceptor::class,
            BenchFinder::class,
            new FilePostfixTestLocatorInterceptor(),
            new TestoAttributesLocatorInterceptor(),
            new AssertCollectorInterceptor(),
            AttributesInterceptor::class,
            new ExpectationsInterceptor(),
            LifecycleInterceptor::class,
        ]);
    }

    /**
     * Get interceptors for
     *
     * @template-covariant T of Interceptor
     *
     * @param class-string<T> $class The target interceptor class.
     * @param class-string<Interceptor>|Interceptor ...$interceptors Interceptor classes or instances
     *        to filter by the given class.
     *
     * @return Interceptor Interceptor instances of the given class.
     */
    public function fromClasses(string $class, string|Interceptor ...$interceptors): array
    {
        $result = [];
        foreach ($interceptors as $interceptor) {
            if (\is_string($interceptor)) {
                if (\class_exists($interceptor) && !\is_a($interceptor, $class, true)) {
                    continue;
                }

                $interceptor = $this->container->get($interceptor);
            }

            $interceptor instanceof $class and $result[] = $interceptor;
        }
        return $result;
    }

    /**
     * Get interceptors for the given attributes set filtered by the given class.
     *
     * @template-covariant T of Interceptor
     *
     * @param class-string<T> $class The target interceptor class.
     * @param Interceptable ...$attributes Attributes to get interceptors for.
     *
     * @return Interceptor Interceptors for the given attributes.
     */
    public function fromAttributes(string $class, Interceptable ...$attributes): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            # Get alias interceptor
            $iClass = Cache::resolveAlias($attribute::class) ?? throw new \RuntimeException(
                \sprintf('No interceptor found for attribute %s.', $attribute::class),
            );

            \is_a($iClass, $class, true) and $result[] = $this->createInstance($iClass, [$attribute]);
        }

        return $result;
    }

    /**
     * Creates an instance of the given class with the given arguments.
     *
     * @template T of Interceptor
     *
     * @param class-string<T> $class The class to create.
     * @param array $arguments The arguments to pass to the constructor.
     *
     * @return Interceptor The created instance.
     */
    private function createInstance(string $class, array $arguments = []): Interceptor
    {
        return $this->injector->make($class, $arguments);
    }
}
