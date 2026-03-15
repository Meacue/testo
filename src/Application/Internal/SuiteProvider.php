<?php

declare(strict_types=1);

namespace Testo\Application\Internal;

use Internal\Container\Container;
use Internal\Path;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Core\Context\SuiteInfo;
use Testo\Filter;

/**
 * Provides test suites.
 *
 * @internal
 * @psalm-internal Testo\Application
 */
final readonly class SuiteProvider
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Find Test Suite by its configuration considering the applied filters.
     */
    public function findSuite(SuiteConfig $config): ?SuiteInfo
    {
        # FILTERING
        $filter = $this->container->get(Filter::class);

        # Filter by suite name
        $filterNames = $filter?->suites ?? [];
        if ($filterNames !== [] && !\in_array($config->name, $filterNames, true)) {
            return null;
        }

        # Filter by path
        if ($filter->paths !== []) {
            /** @var list<Path> $filterPaths */
            $filterPaths = \array_map(Path::create(...), $filter->paths);
            $filterFunc = static function (Path $path) use ($filterPaths): ?Path {
                foreach ($filterPaths as $fp) {
                    if ($fp->match("$path*")) {
                        return $fp;
                    }
                    if ($path->match("$fp*")) {
                        return $path;
                    }
                }

                return null;
            };
            $includes = \array_filter(\array_map($filterFunc, $config->location->includes));

            if ($includes === []) {
                return null;
            }

            $config = $config->with(location: new FinderConfig(\array_unique($includes), $config->location->excludes));
        }

        # Create suite info
        $info = $this->container->make(SuiteFactory::class)->create($config, $filter);
        if ($info->testCases->getCases() !== []) {
            return $info;
        }

        return null;
    }
}
