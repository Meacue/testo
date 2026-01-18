<?php

declare(strict_types=1);

namespace Testo\Tokenizer;

use Internal\Path;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use Symfony\Component\Finder\SplFileInfo;
use Testo\Application\Config\FinderConfig;

/**
 * @implements \IteratorAggregate<string, SplFileInfo>
 */
final class Finder implements \Countable, \IteratorAggregate
{
    private SymfonyFinder $finder;

    /**
     * @param FinderConfig $config Configuration for finder with absolute paths.
     */
    public function __construct(FinderConfig $config)
    {
        $this->finder = (new SymfonyFinder());
        $this->finder->in($config->includeDirs);
        $this->finder->append($config->includeFiles);

        $config->excludeDirs !== [] || $config->excludeFiles !== [] and $this->finder->filter(
            static function (\SplFileInfo $file) use ($config): bool {
                $path = Path::create($file->getRealPath())->absolute();

                # Files in excluded files
                if ($path->isFile() && \in_array((string) $path, $config->excludeFiles, true)) {
                    return false;
                }

                # Directories in excluded dirs
                $target = (string) $path;
                while (!\in_array($target, $config->includeDirs, true)) {
                    if (\in_array($target, $config->excludeDirs, true)) {
                        return false;
                    }

                    $target = \dirname($target);
                }

                return true;
            },
        );
    }

    public function files(): self
    {
        $self = clone $this;
        $self->finder->files();
        return $self;
    }

    public function directories(): self
    {
        $self = clone $this;
        $self->finder->directories();
        return $self;
    }

    public function getIterator(): \IteratorAggregate
    {
        return $this->finder;
    }

    public function count(): int
    {
        return $this->finder->count();
    }

    /**
     * Apply a custom filter to the finder.
     *
     * @param \Closure(SplFileInfo): bool $filter A closure that defines the filtering logic.
     *
     * @psalm-immutable
     */
    public function withFilter(\Closure $filter): self
    {
        $self = clone $this;
        $self->finder->filter($filter);
        return $self;
    }

    public function __clone(): void
    {
        $this->finder = clone $this->finder;
    }
}
