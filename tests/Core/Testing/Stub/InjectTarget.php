<?php

declare(strict_types=1);

namespace Tests\Core\Testing\Stub;

use Testo\Testing\Attribute\Inject;

/**
 * Target with one injectable and one plain property used to verify that only
 * {@see Inject}-marked properties are populated.
 */
class InjectTarget
{
    #[Inject]
    public InjectService $service;

    public InjectService $untouched;
}
