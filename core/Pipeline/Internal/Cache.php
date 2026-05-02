<?php

declare(strict_types=1);

namespace Testo\Pipeline\Internal;

use Testo\Common\Reflection;
use Testo\Pipeline\Attribute\FallbackInterceptor;
use Testo\Pipeline\Attribute\Interceptable;
use Testo\Pipeline\Interceptor;

/**
 * Cached map of interceptable attributes to their interceptors.
 *
 * @internal
 * @psalm-internal Testo\Pipeline
 */
final class Cache
{
    /**
     * @var array<class-string<Interceptable>, null|class-string<Interceptor>>
     */
    private static array $map = [];

    /**
     * Resolve alias interceptor for the given attribute class.
     *
     * @param class-string<Interceptable> $class The attribute class.
     * @return class-string<Interceptor>|null The interceptor class or null if not found.
     */
    public static function resolveAlias(string $class): ?string
    {
        $c = $class;
        do {
            if (\array_key_exists($c, self::$map)) {
                return self::$map[$c];
            }

            $c = \get_parent_class($c);
        } while ($c);

        /**
         * Resolve fallback handler from the {@see FallbackInterceptor} attribute
         * @var list<\ReflectionAttribute<FallbackInterceptor>> $attrs
         */
        $attrs = Reflection::fetchClassAttributes($class, attributeClass: FallbackInterceptor::class);

        return self::$map[$class] ??= $attrs === [] ? null : $attrs[0]->newInstance()->class;
    }
}
