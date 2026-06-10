<?php

declare(strict_types=1);

namespace Testo\Facade\Internal;

use Internal\Container\Container;
use Testo\Facade\Exception\ContainerNotFound;

/**
 * Holds the DI container exposed through the static {@see \Testo}.
 *
 * The container is set by {@see \Testo\Facade\FacadePlugin} when the suite is configured.
 *
 * @internal
 * @psalm-internal Testo\Facade
 */
final class StaticState
{
    public static ?Container $container = null;

    /**
     * The container exposed through the facade, if any.
     */
    public static function current(): Container
    {
        return self::$container ?? throw new ContainerNotFound();
    }
}
