<?php

declare(strict_types=1);

namespace Tests\Fixture;

final class ClassDataProvider
{
    public function __invoke(): iterable
    {
        yield [1, '1'];
        yield [2, '2'];
    }
}
