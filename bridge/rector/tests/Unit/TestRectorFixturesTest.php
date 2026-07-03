<?php

declare(strict_types=1);

namespace Tests\Bridge\Rector\Unit;

use Internal\Path;
use Testo\Assert;
use Testo\Bridge\Rector\Testing\TestRectorFixtures;
use Testo\Codecov\Covers;
use Testo\Test;

#[Test]
#[Covers(TestRectorFixtures::class)]
final class TestRectorFixturesTest
{
    public function noArgumentsYieldNoPaths(): void
    {
        Assert::same((new TestRectorFixtures())->paths, []);
    }

    public function pathsAreConvertedToPathObjects(): void
    {
        $attribute = new TestRectorFixtures('Foo', 'bar/baz');

        Assert::count($attribute->paths, 2);
        Assert::instanceOf($attribute->paths[0], Path::class);
        Assert::same((string) $attribute->paths[0], 'Foo');
        Assert::same((string) $attribute->paths[1], 'bar/baz');
    }
}
