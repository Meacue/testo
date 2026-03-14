<?php

declare(strict_types=1);

namespace Testo\Tokenizer;

use Internal\Path;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Value\Filter;
use Testo\Tokenizer\Reflection\TokenizedFile;

/**
 * Locates and tokenizes PHP files within a given FS scope.
 *
 * Reads files discovered by {@see Finder}, tokenizes their contents,
 * and creates {@see TokenizedFile} objects.
 *
 * @implements \IteratorAggregate<int, TokenizedFile>
 */
final readonly class FileLocator implements \IteratorAggregate
{
    protected Finder $finder;

    public function __construct(
        Finder $finder,
        protected bool $debug = false,
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
    #[\Override]
    public function getIterator(): \Generator
    {
        foreach ($this->finder->getIterator() as $file) {
            yield new TokenizedFile($file, (string) $file);
        }
    }
}
