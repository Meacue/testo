<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Testo\Common\Messenger;
use Testo\Facade\Internal\StaticState;

/**
 * Static access to the services of the suite's DI container.
 *
 * The container is installed by the {@see \Testo\Facade\FacadePlugin} when the suite is
 * configured, so calls made from test code are resolved against the suite container.
 *
 * @internal Experimental
 */
final class Testo
{
    /**
     * Get a PSR-3 logger bound to the given message channel.
     *
     * @param non-empty-string $channel The name of the message channel to log to.
     */
    public static function logger(string $channel): LoggerInterface
    {
        return StaticState::current()
            ->get(Messenger::class)
            ->channel($channel);
    }
}
