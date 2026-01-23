<?php

declare(strict_types=1);

namespace Tests\Common\Stub;

interface MarkedInterface
{
    #[MarkerAttribute]
    public function interfaceMethod(): void;
}
