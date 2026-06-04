<?php

declare(strict_types=1);

namespace Tests\Core\Testing\Stub;

use Testo\Testing\Attribute\Inject;

/**
 * Parent carrying a private injectable property — verifies the injector reaches
 * private properties declared on ancestor classes.
 */
class InjectParent
{
    #[Inject]
    private InjectService $parentService;

    public function parentService(): InjectService
    {
        return $this->parentService;
    }
}
