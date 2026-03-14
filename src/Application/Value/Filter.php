<?php

declare(strict_types=1);

namespace Testo\Application\Value;

use Internal\Path;
use Testo\Application\Input\RunScope;
use Testo\Core\Value\TestType;

/**
 * Immutable DTO containing test filtering criteria.
 *
 * Can be created manually and passed to Application::run() or populated automatically
 * from CLI arguments.
 *
 * @api
 */
final readonly class Filter
{
    /**
     * Test suite names to filter by.
     *
     * @var list<non-empty-string>
     */
    public array $suites;

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
    public array $names;

    /**
     * Absolute file or directory paths to filter by.
     *
     * Supports glob patterns: *, ?, [abc]
     *
     * @var list<Path>
     */
    public array $paths;

    /**
     * Optional type filter for test cases, e.g. 'test', 'unit', 'inline', 'bench', etc.
     * @see TestType
     *
     * @var non-empty-string|null
     */
    public ?string $type;

    /**
     * @param list<non-empty-string> $suites Test suite names to filter by
     * @param list<non-empty-string> $names Class, method, or function names to filter by
     * @param list<non-empty-string|Path> $paths File or directory paths to filter by (supports glob patterns)
     * @param non-empty-string|null $type Optional type filter for test cases
     */
    public function __construct(
        array $suites = [],
        array $names = [],
        array $paths = [],
        ?string $type = null,
    ) {
        $this->suites = $suites;
        $this->names = $names;
        $this->paths = \array_map(static fn(string|Path $p): Path => Path::create($p)->absolute(), $paths);
        $this->type = $type;
    }

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
            type: $scope->type,
        );
    }

    /**
     * Create a new Filter instance with modified properties.
     *
     * @param list<non-empty-string>|null $testSuites New test suite names, or null to keep existing
     * @param list<non-empty-string>|null $names New names, or null to keep existing
     * @param list<non-empty-string|Path>|null $paths New paths, or null to keep existing
     * @param string|null $type New type, empty string to set null, or null to keep existing
     *
     * @return self New Filter instance with updated properties
     */
    public function with(
        ?array $testSuites = null,
        ?array $names = null,
        ?array $paths = null,
        ?string $type = null,
    ): self {
        return new self(
            suites: $testSuites ?? $this->suites,
            names: $names ?? $this->names,
            paths: $paths ?? $this->paths,
            type: match ($type) {
                null => $this->type,
                '' => null,
                default => $type,
            },
        );
    }
}
