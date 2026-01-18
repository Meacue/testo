<?php

declare(strict_types=1);

namespace Testo\Pipeline\Internal;

use Testo\Common\Reflection;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Internal\InterceptorMarker as TInterceptor;
use Testo\Pipeline\Policy\ConflictPolicy;

/**
 * Sorts and filters interceptors.
 * @internal
 */
final class Sorter
{
    /** @var array<class-string<TInterceptor>, ConflictPolicy> */
    private static array $conflictPolicyCache = [];

    /** @var array<class-string<TInterceptor>, int> */
    private static array $orderCache = [];

    /**
     * Sort and filter interceptors.
     *
     * @param TInterceptor[] $interceptors
     *
     * @return list<TInterceptor>
     */
    public static function sortAndFilter(array $interceptors): array
    {
        # Local caches
        $conflicts = [];
        $orders = [];

        /** @var TInterceptor $groups */
        $groups = [];
        foreach ($interceptors as $interceptor) {
            $class = $interceptor::class;
            $conflict = $conflicts[$class] ??= self::getConflictPolicy($interceptor);
            $order = $orders[$class] ??= self::getOrder($interceptor);

            switch ($conflict) {
                case ConflictPolicy::First:
                    $groups[$order][$class] ??= $interceptor;
                    break;
                case ConflictPolicy::Last:
                    unset($groups[$order][$class]);
                    $groups[$order][$class] = $interceptor;
                    break;
                case ConflictPolicy::Error:
                    \array_key_exists($class, $groups[$order])
                        ? throw new \RuntimeException("Conflict detected for interceptor '$class' with policy 'Error'.")
                        : $groups[$order][$class] = $interceptor;
                    break;
                default:
                    $groups[$order][] = $interceptor;
                    break;
            }
        }

        \ksort($groups);
        return \array_values(\array_merge(...$groups));
    }

    /**
     * Warm up the cache for the given interceptor class.
     *
     * @param class-string<TInterceptor> $class
     */
    private static function warmUpCache(string $class): void
    {
        /** @var list<\ReflectionAttribute<InterceptorOptions>> $attributes */
        $attributes = Reflection::fetchClassAttributes(
            $class,
            attributeClass: InterceptorOptions::class,
        );

        if ($attributes === []) {
            self::$conflictPolicyCache[$class] = ConflictPolicy::default();
            self::$orderCache[$class] = InterceptorOptions::ORDER_DEFAULT;
            return;
        }

        $attribute = $attributes[0]->newInstance();
        self::$conflictPolicyCache[$class] = $attribute->onConflict;
        self::$orderCache[$class] = $attribute->order;
    }

    /**
     * Get the conflict policy of the given interceptor by its attribute.
     */
    private static function getConflictPolicy(TInterceptor $interceptor): ConflictPolicy
    {
        $class = $interceptor::class;
        \array_key_exists($class, self::$conflictPolicyCache) or self::warmUpCache($class);
        return self::$conflictPolicyCache[$class];
    }

    /**
     * Get the order of the given interceptor by its attribute.
     */
    private static function getOrder(TInterceptor $interceptor): int
    {
        $class = $interceptor::class;
        \array_key_exists($class, self::$orderCache) or self::warmUpCache($class);
        return self::$orderCache[$class];
    }
}
