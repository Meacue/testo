<?php

declare(strict_types=1);

namespace Tests\Testo\Unit;

use Testo\Common\Info;
use Testo\Test;

final class InfoTest
{
    #[Test]
    public function testVersionDoesntFail(): void
    {
        Info::version();
    }
}
