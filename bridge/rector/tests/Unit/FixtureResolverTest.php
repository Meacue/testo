<?php

declare(strict_types=1);

namespace Tests\Bridge\Rector\Unit;

use Internal\Path;
use Testo\Assert;
use Testo\Bridge\Rector\Testing\Internal\FixtureResolver;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

#[Test]
#[Covers(FixtureResolver::class)]
final class FixtureResolverTest
{
    /** Absolute path to the sibling directory holding alpha.php.inc / beta.php.inc. */
    private const FIXTURES = __DIR__ . '/Fixtures';

    public function resolvesRelativeDirectoryAgainstRuleDir(): void
    {
        $map = (new FixtureResolver(__DIR__))->resolve(Path::create(__DIR__), [Path::create('Fixtures')]);

        Assert::count($map, 2);
        Assert::contains(\array_keys($map), 'alpha.php.inc');
        Assert::contains(\array_keys($map), 'beta.php.inc');
        Assert::instanceOf($map['alpha.php.inc'], Path::class);
    }

    public function resolvesAbsoluteDirectoryIgnoringRuleDir(): void
    {
        // A bogus rule directory proves the absolute path is used as-is, not joined to it.
        $map = (new FixtureResolver(__DIR__))
            ->resolve(Path::create(__DIR__ . '/does/not/matter'), [Path::create(self::FIXTURES)]);

        Assert::count($map, 2);
    }

    public function resolvesAbsoluteFileToSingleFixture(): void
    {
        $map = (new FixtureResolver(__DIR__))
            ->resolve(Path::create(__DIR__), [Path::create(self::FIXTURES . '/alpha.php.inc')]);

        Assert::same(\array_keys($map), ['alpha.php.inc']);
        Assert::instanceOf($map['alpha.php.inc'], Path::class);
    }

    public function emptyPathsResolveToNothing(): void
    {
        Assert::same((new FixtureResolver(__DIR__))->resolve(Path::create(__DIR__), []), []);
    }

    public function rejectsRelativePathEscapingRoot(): never
    {
        Expect::exception(\LogicException::class);

        (new FixtureResolver(__DIR__))->resolve(Path::create(__DIR__), [Path::create('../Fixtures')]);
    }

    public function rejectsAbsolutePathOutsideRoot(): never
    {
        Expect::exception(\LogicException::class);

        // The tests/ directory is the parent of this root — an absolute path pointing there escapes it.
        (new FixtureResolver(__DIR__))->resolve(Path::create(__DIR__), [Path::create(\dirname(__DIR__))]);
    }
}
