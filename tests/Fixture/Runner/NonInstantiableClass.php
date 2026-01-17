<?php

declare(strict_types=1);

namespace Tests\Fixture\Runner;

final class NonInstantiableClass
{
    public function __construct(string $required) {}
}