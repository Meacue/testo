<?php

declare(strict_types=1);

namespace Tests\Testo\Acceptance;

use Symfony\Component\Process\Process;
use Testo\Assert;
use Testo\Test;

/**
 * Black-box acceptance test for the release-time constraint synchroniser
 * (.github/release-please/sync-deps.php).
 *
 * The script is procedural and resolves paths via getcwd(), so each case builds
 * an isolated fixture monorepo in a temp directory and runs the real script
 * there as a subprocess, then inspects the resulting composer.json files.
 *
 * No #[Covers]: the subject is a standalone CI script, not an autoloaded class.
 */
#[Test]
final class SyncDepsTest
{
    private const SCRIPT = '/.github/release-please/sync-deps.php';

    public function pinsRequireConstraintsToManifestVersions(): void
    {
        $dir = $this->fixture(
            ['.' => '1.0.0', 'plugin/a' => '1.2.3', 'plugin/b' => '0.5.0'],
            [
                'composer.json' => $this->composer('testo/testo', [
                    'php' => '>=8.2',
                    'testo/a' => '0.1 - 1',
                    'testo/b' => '0.1 - 1',
                ]),
                'plugin/a/composer.json' => $this->composer('testo/a', [
                    'testo/b' => '0.1 - 1',
                    'testo/testo' => '*',
                ]),
                'plugin/b/composer.json' => $this->composer('testo/b'),
            ],
        );

        try {
            $this->run($dir);

            $root = $this->requireOf($dir, 'composer.json');
            Assert::same($root['testo/a'], '^1.2.3');
            Assert::same($root['testo/b'], '^0.5.0');
            Assert::same($root['php'], '>=8.2', 'non-testo dependency must stay untouched');

            $a = $this->requireOf($dir, 'plugin/a/composer.json');
            Assert::same($a['testo/b'], '^0.5.0');
            Assert::same($a['testo/testo'], '^1.0.0', 'root package is pinned by its real name');
        } finally {
            $this->cleanup($dir);
        }
    }

    public function syncsRequireDevSection(): void
    {
        $dir = $this->fixture(
            ['.' => '1.0.0', 'bridge/infection' => '2.0.1'],
            [
                'composer.json' => $this->composer('testo/testo', [], [
                    'testo/bridge-infection' => '0.1 - 1',
                    'phpunit/phpunit' => '^10',
                ]),
                'bridge/infection/composer.json' => $this->composer('testo/bridge-infection'),
            ],
        );

        try {
            $this->run($dir);

            $dev = $this->read($dir, 'composer.json')['require-dev'];
            Assert::same($dev['testo/bridge-infection'], '^2.0.1');
            Assert::same($dev['phpunit/phpunit'], '^10', 'non-testo dev dependency must stay untouched');
        } finally {
            $this->cleanup($dir);
        }
    }

    public function leavesReplaceSuggestAndPathRepositoriesUntouched(): void
    {
        $root = [
            'name' => 'testo/testo',
            'require' => ['testo/a' => '0.1 - 1'],
            'suggest' => ['testo/a' => 'Some description — not a version constraint.'],
            'replace' => ['internal/foo' => '*'],
            'repositories' => [[
                'type' => 'path',
                'url' => 'plugin/*',
                'options' => ['versions' => ['testo/a' => '0.1.x-dev']],
            ]],
        ];

        $dir = $this->fixture(
            ['.' => '1.0.0', 'plugin/a' => '1.2.3'],
            [
                'composer.json' => $this->encode($root),
                'plugin/a/composer.json' => $this->composer('testo/a'),
            ],
        );

        try {
            $this->run($dir);

            $decoded = $this->read($dir, 'composer.json');
            Assert::same($decoded['require']['testo/a'], '^1.2.3', 'require is synced');
            Assert::same($decoded['suggest']['testo/a'], 'Some description — not a version constraint.');
            Assert::same($decoded['replace']['internal/foo'], '*');
            Assert::same($decoded['repositories'][0]['options']['versions']['testo/a'], '0.1.x-dev');
        } finally {
            $this->cleanup($dir);
        }
    }

    public function leavesUnmanagedTestoPackagesUntouched(): void
    {
        $dir = $this->fixture(
            ['.' => '1.0.0'],
            ['composer.json' => $this->composer('testo/testo', ['testo/not-in-manifest' => '0.1 - 1'])],
        );

        try {
            $this->run($dir);

            $root = $this->requireOf($dir, 'composer.json');
            Assert::same($root['testo/not-in-manifest'], '0.1 - 1');
        } finally {
            $this->cleanup($dir);
        }
    }

    public function isIdempotent(): void
    {
        $dir = $this->fixture(
            ['.' => '1.0.0', 'plugin/a' => '1.2.3'],
            [
                'composer.json' => $this->composer('testo/testo', ['testo/a' => '0.1 - 1']),
                'plugin/a/composer.json' => $this->composer('testo/a'),
            ],
        );

        try {
            $this->run($dir);
            $first = (string) \file_get_contents($dir . '/composer.json');

            $output = $this->run($dir);
            $second = (string) \file_get_contents($dir . '/composer.json');

            Assert::same($second, $first, 'a second run must not change anything');
            Assert::true(\str_contains($output, 'No intra-package constraints'));
        } finally {
            $this->cleanup($dir);
        }
    }

    /**
     * @param array<string, string> $manifest path => version
     * @param array<string, string> $composers relative path => json content
     */
    private function fixture(array $manifest, array $composers): string
    {
        $dir = \sys_get_temp_dir() . '/testo-syncdeps-' . \bin2hex(\random_bytes(6));
        \mkdir($dir . '/resources', 0o777, true);
        \file_put_contents($dir . '/resources/version.json', $this->encode($manifest));

        foreach ($composers as $relative => $content) {
            $path = $dir . '/' . $relative;
            $parent = \dirname($path);
            \is_dir($parent) or \mkdir($parent, 0o777, true);
            \file_put_contents($path, $content);
        }

        return $dir;
    }

    /**
     * @param array<string, string> $require
     * @param array<string, string> $requireDev
     */
    private function composer(string $name, array $require = [], array $requireDev = []): string
    {
        $data = ['name' => $name];
        $require === [] or $data['require'] = $require;
        $requireDev === [] or $data['require-dev'] = $requireDev;

        return $this->encode($data);
    }

    private function run(string $cwd): string
    {
        $script = \dirname(__DIR__, 3) . self::SCRIPT;
        $process = new Process([\PHP_BINARY, $script], $cwd);
        $process->run();

        Assert::true($process->isSuccessful(), 'sync-deps.php exited with: ' . $process->getErrorOutput());

        return $process->getOutput();
    }

    /** @return array<string, mixed> */
    private function read(string $dir, string $relative): array
    {
        return \json_decode((string) \file_get_contents($dir . '/' . $relative), true, flags: \JSON_THROW_ON_ERROR);
    }

    /** @return array<string, string> */
    private function requireOf(string $dir, string $relative): array
    {
        return $this->read($dir, $relative)['require'];
    }

    private function encode(mixed $data): string
    {
        return (string) \json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
    }

    private function cleanup(string $dir): void
    {
        if (!\is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            $item->isDir() ? \rmdir($item->getPathname()) : \unlink($item->getPathname());
        }
        \rmdir($dir);
    }
}
