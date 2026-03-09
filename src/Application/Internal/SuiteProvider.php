<?php

declare(strict_types=1);

namespace Testo\Application\Internal;

use Testo\Application\Config\SuiteConfig;
use Testo\Application\Value\Filter;
use Testo\Common\Container;
use Testo\Core\Context\SuiteInfo;

/**
 * Provides test suites.
 *
 * @internal
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
        $filter = $this->container->get(Filter::class);
        $filterNames = $filter?->suites ?? [];

        // Apply suite name filter
        if ($filterNames !== [] && !\in_array($config->name, $filterNames, true)) {
            return null;
        }

        $info = $this->container->make(SuiteFactory::class)->create($config, $filter);
        if ($info->testCases->getCases() !== []) {
            return $info;
        }

        return null;
    }
}
