<?php

declare(strict_types=1);

namespace Tests\Test\Unit\Fixture;

use Testo\Test;

#[Test]
abstract class AbstractTestClassWithVoidMethods
{
    public function concreteVoidMethod(): void {}

    abstract public function abstractVoidMethod(): void;

    abstract public function abstractNeverMethod(): never;
}
