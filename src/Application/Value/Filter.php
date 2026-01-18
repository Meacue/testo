<?php

declare(strict_types=1);

namespace Testo\Application\Value;

use Testo\Application\Input\RunScope;

/**
 * Immutable DTO containing test filtering criteria.
 *
 * Can be created manually and passed to Application::run() or populated automatically
 * from CLI arguments.
 */
final class Filter
{
    public function __construct(
        /**
         * Test suite names to filter by.
         *
         * @var list<non-empty-string>
         */
        public readonly array $suites = [],

        /**
         * Class, method, or function names to filter by.
         *
         * Supports formats:
         * - Method: ClassName::methodName or Namespace\ClassName::methodName
         * - FQN: Namespace\ClassName or Namespace\functionName
         * - Fragment: methodName, functionName, or ShortClassName
         *
         * @var list<non-empty-string>
         */
        public readonly array $names = [],

        /**
         * File or directory paths to filter by.
         *
         * Supports glob patterns: *, ?, [abc]
         *
         * @var list<non-empty-string>
         */
        public readonly array $paths = [],
    ) {}

    /**
     * Create Filter from RunScope populated by CLI arguments.
     *
     * Automatically categorizes filter values from CLI:
     * - Values containing dots or existing file paths → paths
     * - Other values → names
     * - Suite values → testSuites
     *
     * @param RunScope $scope Configuration scope with CLI arguments
     *
     * @return self New Filter instance with categorized criteria
     */
    public static function fromScope(RunScope $scope): self
    {
        // TODO remove in the future
        $files = \array_filter(
            $scope->filter,
            static fn($value) => \str_contains($value, '.') || \file_exists($value),
        );
        $filter = \array_diff($scope->filter, $files);

        return new self(
            suites: $scope->suite,
            names: $filter,
            paths: \array_merge($scope->path, $files),
        );
    }

    /**
     * Create a new Filter instance with modified properties.
     *
     * @param list<non-empty-string>|null $testSuites New test suite names, or null to keep existing
     * @param list<non-empty-string>|null $names New names, or null to keep existing
     * @param list<non-empty-string>|null $paths New paths, or null to keep existing
     *
     * @return self New Filter instance with updated properties
     */
    public function with(
        ?array $testSuites = null,
        ?array $names = null,
        ?array $paths = null,
    ): self {
        return new self(
            suites: $testSuites ?? $this->suites,
            names: $names ?? $this->names,
            paths: $paths ?? $this->paths,
        );
    }
}
