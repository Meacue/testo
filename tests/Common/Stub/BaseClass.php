<?php

declare(strict_types=1);

namespace Tests\Common\Stub;

abstract class BaseClass
{
    #[MarkerAttribute]
    public function baseMethod(): void {}

    public function unmarkedMethod(): void {}

    #[MarkerAttribute]
    public function overriddenMethod(): void {}
}
