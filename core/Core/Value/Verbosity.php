<?php

declare(strict_types=1);

namespace Testo\Core\Value;

/**
 * Output verbosity level, independent of any particular CLI framework.
 *
 * Mirrors the conventional five console levels (and Symfony's `-v`/`-vv`/`-vvv` ladder). Backed by
 * an ordinal so levels can be compared with {@see atLeast()} — e.g. "stream live output only when
 * the verbosity is at least {@see Verbosity::Verbose}".
 *
 * @api
 */
enum Verbosity: int
{
    case Quiet = 0;
    case Normal = 1;
    case Verbose = 2;
    case VeryVerbose = 3;
    case Debug = 4;

    /**
     * Whether this verbosity is at least as high as the given level.
     */
    public function atLeast(self $level): bool
    {
        return $this->value >= $level->value;
    }
}
