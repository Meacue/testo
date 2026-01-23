<?php

declare(strict_types=1);

namespace Tests\Common\Stub;

class MiddleClass extends BaseClass
{
    #[AnotherMarkerAttribute]
    public function middleMethod(): void {}

    public function overriddenMethod(): void {}
}
