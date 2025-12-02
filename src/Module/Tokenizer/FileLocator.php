<?php

declare(strict_types=1);

namespace Testo\Module\Tokenizer;

use Internal\Path;
use Testo\Common\Filter;
use Testo\Config\FinderConfig;
use Testo\Module\Finder\Finder;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;

/**
 * Locates and tokenizes PHP files within a given FS scope.
 *
 * Reads files discovered by {@see Finder}, tokenizes their contents,
 * and creates {@see TokenizedFile} objects.
 *
 * @implements \IteratorAggregate<int, TokenizedFile>
 */
final class FileLocator implements \IteratorAggregate
{
    protected readonly Finder $finder;

    public function __construct(
        Finder $finder,
        protected readonly bool $debug = false,
    ) {
        $this->finder = $finder->files();
    }

    public static function fromFinderConfig(FinderConfig $config, Filter $filter = new Filter()): self
    {
        $finder = new Finder($config);
        $toFilter = $filter->paths;
        $toFilter === [] or $finder = $finder->withFilter(
            static function (\SplFileInfo $info) use ($toFilter): bool {
                foreach ($toFilter as $pattern) {
                    $path = Path::create($info->getRealPath());
                    if ($path->match("$pattern*")) {
                        return true;
                    }
                }

                return false;
            },
        );

        return new self(
            $finder,
        );
    }

    /**
     * Available file reflections. Generator.
     *
     * @return \Generator<int, TokenizedFile, mixed, void>
     * @throws \Exception
     */
    public function getIterator(): \Generator
    {
        foreach ($this->finder->getIterator() as $file) {
            yield new TokenizedFile($file, (string) $file);
        }
    }
}
