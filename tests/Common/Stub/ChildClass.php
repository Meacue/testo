<?php

declare(strict_types=1);

namespace Tests\Common\Stub;

class ChildClass extends MiddleClass implements MarkedInterface
{
    #[MarkerAttribute]
    public function childMethod(): void {}

    public function interfaceMethod(): void {}
}
