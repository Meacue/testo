<?php

declare(strict_types=1);

namespace Tests\Testo\Unit;

use Testo\Common\Info;

final class InfoTest
{
    public function testVersionDoesntFail(): void
    {
        Info::version();
    }
}
