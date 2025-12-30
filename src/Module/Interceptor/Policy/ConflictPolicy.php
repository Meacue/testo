<?php

declare(strict_types=1);

namespace Testo\Module\Interceptor\Policy;

/**
 * Merge policy for interceptors.
 *
 * Determines how to handle multiple interceptors of the same type.
 */
enum ConflictPolicy
{
    /**
     * Use the first interceptor.
     * Default behavior.
     */
    case First;

    /**
     * Do nothing: all the interceptors will be applied.
     */
    case Merge;

    /**
     * Use the last interceptor.
     */
    case Last;

    /**
     * Break the pipeline with an error.
     */
    case Error;

    public static function default(): self
    {
        return self::First;
    }
}
