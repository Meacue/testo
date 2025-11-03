<?php

declare(strict_types=1);

namespace Testo\Common;

use Testo\Common\Input\RunScope;

/**
 * Filter tests by various criteria.
 *
 * todo: Implement filtering logic.
 */
final class Filter
{
    use CloneWith;

    // private readonly $conditionFile

    public function __construct(
        /**
         * @var list<non-empty-string> Names of the test suites to filter by.
         */
        public readonly array $testSuites = [],

        /**
         * @var list<non-empty-string> List of class, method, or function names to filter by.
         */
        public readonly array $names = [],

        /**
         * @var list<non-empty-string> List of file or dir paths to filter by.
         */
        public readonly array $paths = [],
    ) {}

    public static function fromScope(RunScope $scope): self
    {
        // TODO remove in the future
        $files = \array_filter(
            $scope->filter,
            static fn($value) => \str_contains($value, '.') || \file_exists($value),
        );
        $filter = \array_diff($scope->filter, $files);

        return new self(
            testSuites: $scope->suite,
            names: $filter,
            paths: \array_merge($scope->path, $files),
        );
    }

    /**
     * Filter tests by Suite names.
     *
     * @param non-empty-string ...$names Names of the test suites to filter by.
     *
     * @return self A new instance of Filter with the specified test names.
     */
    public function withTestSuites(string ...$names): self
    {
        return $this->cloneWith('testSuites', \array_unique(\array_merge($this->testSuites, $names)));
    }

    public function withTestCases($name): self
    {
        // TODO
        return $this;
    }
}
