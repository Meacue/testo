<?php

declare(strict_types=1);

namespace Testo\Application\Internal;

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Application\Value\Filter;
use Testo\Common\CloneWith;
use Testo\Core\Context\SuiteInfo;

/**
 * Provides test suites.
 *
 * @internal
 */
final readonly class SuiteProvider
{
    use CloneWith;

    /** @var list<SuiteConfig> */
    private array $configs;

    private ?Filter $filter;

    public function __construct(
        ApplicationConfig $applicationConfig,
        private SuiteCollector $collector,
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
     * @return array<array{SuiteConfig, SuiteInfo}>
     */
    public function getSuites(): array
    {
        $result = [];
        $filterNames = $this->filter?->suites ?? [];

        foreach ($this->configs as $config) {
            // Apply suite name filter
            if ($filterNames !== [] && !\in_array($config->name, $filterNames, true)) {
                continue;
            }

            $info = $this->collector->getOrCreate($config, $this->filter);
            if ($info->testCases->getCases() !== []) {
                $result[] = [$config, $info];
            }
        }

        return $result;
    }
}
