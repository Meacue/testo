<?php

declare(strict_types=1);

namespace Tests\Core\Testing\Stub;

use Testo\Testing\Attribute\Inject;

/**
 * Misconfigured target: a builtin-typed {@see Inject} property cannot be resolved from the container.
 */
final class InvalidInjectTarget
{
    #[Inject]
    public int $count;
}
