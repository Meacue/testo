<?php

declare(strict_types=1);

namespace Testo\Bridge\Rector\Testing\Internal;

use Internal\Path;
use Rector\Testing\Fixture\FixtureFileFinder;
use Testo\Bridge\Rector\Testing\TestRectorFixtures;

/**
 * Resolves the fixture paths a rule declares via {@see TestRectorFixtures} to concrete fixture
 * files.
 *
 * A relative path is taken against the rule's own source directory; an absolute path is used
 * as-is. In both cases the resolved location MUST stay within the configured root (the working
 * directory): a path that escapes it — through `..` or an absolute path pointing elsewhere — is
 * rejected with a {@see \LogicException} instead of being silently read, so a rule can never pull
 * fixtures from outside the project it is tested in.
 *
 * A directory yields its `*.php.inc` fixtures; a file is taken as the single fixture.
 *
 * @internal
 * @psalm-internal Testo\Bridge\Rector
 */
final readonly class FixtureResolver
{
    private Path $root;

    public function __construct(Path|string $root)
    {
        // Absolutize once so the containment check below compares like with like.
        $this->root = Path::create($root)->absolute();
    }

    /**
     * @param iterable<Path> $paths Declared fixture paths (relative to $ruleDir, or absolute).
     *
     * @return array<non-empty-string, Path> Fixture file name => absolute path.
     *
     * @throws \LogicException When a declared path resolves outside the root.
     */
    public function resolve(Path $ruleDir, iterable $paths): array
    {
        $ruleDir = $ruleDir->absolute();

        $map = [];
        foreach ($paths as $path) {
            $candidate = $path->isAbsolute() ? $path : $ruleDir->join($path);

            // Containment guard: Path::absolute() validates that an absolute path starts with the
            // root and throws otherwise — reused here as the "stay within CWD" check.
            $safe = $candidate->absolute((string) $this->root);

            if ($safe->isDir()) {
                foreach (FixtureFileFinder::yieldDirectory((string) $safe) as [$file]) {
                    $file = Path::create($file);
                    $map[$file->name()] = $file;
                }
            } elseif ($safe->isFile()) {
                $map[$safe->name()] = $safe;
            }
        }

        return $map;
    }
}
