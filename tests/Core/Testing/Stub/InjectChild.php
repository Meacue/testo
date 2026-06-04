<?php

declare(strict_types=1);

namespace Tests\Core\Testing\Stub;

use Testo\Testing\Attribute\Inject;

/**
 * Child adding its own injectable property on top of {@see InjectParent}.
 */
final class InjectChild extends InjectParent
{
    #[Inject]
    public InjectService $childService;
}
