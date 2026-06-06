<?php

/**
 * Synchronises intra-`testo/*` version constraints across every composer.json
 * in the split-monorepo.
 *
 * Intended to run inside the release workflow, RIGHT AFTER release-please has
 * created/updated the release PR, while the working tree is checked out on the
 * release PR branch. At that point resources/version.json already holds the
 * versions that this release cycle will publish, so we just mirror them.
 *
 * For every `testo/*` entry in the `require` / `require-dev` sections we set the
 * constraint to `^<version>`, where <version> is taken from the manifest
 * (resources/version.json) by the package's real composer name.
 *
 * Notes:
 *  - Only `require` and `require-dev` are touched. `replace`, `suggest` and the
 *    `repositories[].options.versions` dev-aliases are deliberately left alone.
 *  - Editing is surgical (regex on the matched section block), so untouched
 *    lines keep their exact formatting — re-runs produce no spurious diff.
 *  - Bumping a constraint here does NOT bump that package's own version: the
 *    release version is decided by release-please from conventional commits.
 *    So "only B changed" never drags A into a release — A's composer.json may
 *    point at the newer B, but A is re-published only on its own next release.
 *
 * Exit code is always 0; it prints a summary of what changed. Git add/commit/
 * push is handled by the workflow, not here.
 */

declare(strict_types=1);

const MANIFEST = 'resources/version.json';
const SECTIONS = ['require', 'require-dev'];

$root = \getcwd();

$manifest = readJson($root . '/' . MANIFEST);

// Build map: real composer package name => target version, from the manifest.
$versions = [];
foreach ($manifest as $path => $version) {
    $composerPath = $path === '.' ? 'composer.json' : "$path/composer.json";
    $composer = readJson($root . '/' . $composerPath);
    $name = $composer['name'] ?? null;
    if (!\is_string($name) || $name === '') {
        \fwrite(\STDERR, "Skipping `$composerPath`: missing package name\n");
        continue;
    }
    $versions[$name] = (string) $version;
}

// Every composer.json that may reference a testo/* package.
$files = \array_merge(
    [$root . '/composer.json'],
    globFiles($root, 'plugin/*/composer.json'),
    globFiles($root, 'bridge/*/composer.json'),
);

$changed = [];
foreach ($files as $file) {
    if (!\is_file($file)) {
        continue;
    }
    $original = (string) \file_get_contents($file);
    $updated = syncFile($original, $versions);
    if ($updated !== $original) {
        \file_put_contents($file, $updated);
        $changed[] = substr($file, \strlen($root) + 1);
    }
}

if ($changed === []) {
    echo "No intra-package constraints needed updating.\n";
} else {
    echo "Synced testo/* constraints in:\n";
    foreach ($changed as $rel) {
        echo "  - $rel\n";
    }
}

exit(0);

/**
 * Rewrite testo/* constraints in the require / require-dev blocks only.
 *
 * @param array<string, string> $versions package name => version
 */
function syncFile(string $content, array $versions): string
{
    foreach (SECTIONS as $section) {
        // require/require-dev values are plain strings, so the block contains
        // no nested braces — [^{}]* captures the whole section body safely.
        $pattern = '/("' . preg_quote($section, '/') . '"\s*:\s*\{)([^{}]*)(\})/';

        $content = (string) \preg_replace_callback($pattern, static function (array $m) use ($versions): string {
            $body = \preg_replace_callback(
                '/("(testo\/[^"]+)"\s*:\s*")([^"]*)(")/',
                static function (array $dep) use ($versions): string {
                    $name = $dep[2];
                    if (!isset($versions[$name])) {
                        return $dep[0]; // not a managed split package — leave as-is
                    }
                    return $dep[1] . '^' . $versions[$name] . $dep[4];
                },
                $m[2],
            );

            return $m[1] . $body . $m[3];
        }, $content);
    }

    return $content;
}

/** @return array<string, mixed> */
function readJson(string $path): array
{
    if (!\is_file($path)) {
        \fwrite(\STDERR, "File not found: $path\n");
        exit(1);
    }
    $data = \json_decode((string) \file_get_contents($path), true, flags: \JSON_THROW_ON_ERROR);
    \assert(\is_array($data));
    return $data;
}

/**
 * Cross-platform glob relative to a base dir, returning absolute paths.
 *
 * @return list<string>
 */
function globFiles(string $base, string $pattern): array
{
    $matches = \glob($base . '/' . $pattern, \GLOB_NOSORT);
    return $matches === false ? [] : $matches;
}
