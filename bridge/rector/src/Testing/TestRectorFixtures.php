<?php

declare(strict_types=1);

namespace Testo\Bridge\Rector\Testing;

use Internal\Path;

/**
 * Declares the fixtures that exercise a Rector rule, so the rule can be tested "inline":
 * the rule itself points at its fixtures, which the bridge's test harness
 * ({@see Internal\Middleware\RectorFixtureFinder}) discovers and runs.
 *
 * A relative path is resolved against the rule's own source directory; an absolute path is used
 * as-is. Either way the resolved location must stay within the working directory — a path that
 * escapes it (via `..` or an absolute path pointing elsewhere) is rejected by the resolver
 * ({@see Internal\FixtureResolver}). A directory is scanned for `*.php.inc` fixtures; a file is
 * taken as-is. Each fixture holds the input and the expected output separated by a `-----` line
 * (no separator = the rule must leave the input unchanged).
 *
 * No paths = nothing to test: a rule that declares the attribute without arguments contributes no
 * fixtures (there is no implicit default directory).
 *
 * The attribute and the harness ship with the package (so downstream rule authors can reuse
 * them); the fixtures (`*.php.inc`) are `export-ignore`d (see `.gitattributes`).
 *
 * @api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class TestRectorFixtures
{
    /** @var list<Path> */
    public array $paths;

    /**
     * @param non-empty-string ...$paths Directories or files, relative to the rule's file or
     *        absolute. Omit to declare no fixtures.
     */
    public function __construct(string ...$paths)
    {
        $this->paths = \array_map(
            static fn(string $path): Path => Path::create($path),
            \array_values($paths),
        );
    }
}
