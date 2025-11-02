<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Common\Filter;
use Testo\Common\CloneWith;
use Testo\Config\ApplicationConfig;
use Testo\Config\SuiteConfig;
use Testo\Test\Dto\SuiteInfo;

/**
 * Provides test suites.
 */
final class SuiteProvider
{
    use CloneWith;

    /** @var list<SuiteConfig> */
    private readonly array $configs;

    private readonly ?Filter $filter;

    public function __construct(
        ApplicationConfig $applicationConfig,
        private readonly SuiteCollector $collector,
    ) {
        $this->configs = $applicationConfig->suites;
        $this->filter = null;
    }

    /**
     * @psalm-immutable
     */
    public function withFilter(Filter $filter): self
    {
        return $this->cloneWith('filter', $filter);
    }

    /**
     * Gets test suite definitions with applied filter.
     *
     * @return array<SuiteInfo>
     */
    public function getSuites(): array
    {
        $result = [];
        $filterNames = $this->filter?->testSuites ?? [];

        foreach ($this->configs as $config) {
            // Apply suite name filter
            if ($filterNames !== [] && !\in_array($config->name, $filterNames, true)) {
                continue;
            }

            $info = $this->collector->getOrCreate($config);
            if ($info->testCases->getCases() !== []) {
                $result[] = $info;
            }
        }

        return $result;
    }
}
