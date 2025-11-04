<?php

declare(strict_types=1);

namespace Testo\Interceptor\Locator;

use Testo\Common\Filter;
use Testo\Interceptor\CaseLocatorInterceptor;
use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Module\Tokenizer\Reflection\FileDefinitions;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;
use Testo\Test\Dto\CaseDefinitions;
use Testo\Test\Dto\TestDefinitions;

/**
 * Two-stage interceptor for filtering test execution by name patterns.
 *
 * Stage 1 (FileLocatorInterceptor): Pre-filters test files before loading for reflection analysis.
 * Stage 2 (CaseLocatorInterceptor): Filters test cases and individual tests before execution.
 *
 * Supports three filter formats:
 * - FQN: Fully qualified names like `Namespace\ClassName` or `Namespace\functionName`
 * - Method: Class and method pairs like `ClassName::methodName` or `Namespace\ClassName::methodName`
 * - Fragment: Partial names like `methodName`, `functionName`, or `ShortClassName`
 *
 * Filtering logic (OR across all patterns):
 * - If test case name (class name) matches: entire case passes with all tests
 * - If test case name doesn't match: filter individual methods/functions
 *   - If any methods/functions match: case passes with only matched tests
 *   - If no methods/functions match: case is skipped entirely
 */
final class FilterInterceptor implements FileLocatorInterceptor, CaseLocatorInterceptor
{
    /** @var bool True if filtering is disabled (no filters provided) */
    private readonly bool $skip;

    /**
     * Fully qualified names to filter by. Like `Namespace\ClassName` or `Namespace\functionName`.
     * @var list<non-empty-string>
     */
    private readonly array $fqn;

    /**
     * Possible method names to filter by. Like `ClassName::methodName`.
     *
     * @var list<array{non-empty-string, non-empty-string}>
     */
    private readonly array $method;

    /**
     * Partial name fragment to filter by. Like `methodName`, `shortFunctionName`, or `ShortClassName`.
     *
     * @var list<non-empty-string>
     */
    private readonly array $fragment;

    public function __construct(
        Filter $filter,
    ) {
        $fqn = $method = $fragment = [];
        foreach ($filter->names as $name) {
            if (\str_contains($name, '::')) {
                $method[] = \explode('::', \ltrim($name, '\\'), 2);
            } elseif (\str_contains($name, '\\')) {
                $fqn[] = \trim($name, '\\');
            } else {
                $fragment[] = $name;
            }
        }

        $this->skip = $fqn === [] && $method === [] && $fragment === [];
        $this->fqn = $fqn;
        $this->method = $method;
        $this->fragment = $fragment;
    }

    /**
     * Stage 1: Filter test files before loading for reflection analysis.
     *
     * Performs quick pre-filtering based on tokenized file data to skip files
     * that don't contain any matching classes, methods, or functions.
     *
     * @param TokenizedFile $file Tokenized file with class/function/method names
     * @param callable(TokenizedFile): (null|bool) $next Next interceptor in the chain
     *
     * @return bool|null True to include file, false to skip, null for passthrough
     */
    public function locateFile(TokenizedFile $file, callable $next): ?bool
    {
        return match (true) {
            $this->skip,
            $this->matchFile($file) => $next($file),
            default => false,
        };
    }

    /**
     * Stage 2: Filter test cases and methods after reflection analysis.
     *
     * Filters loaded test definitions based on class and method names:
     * - If class name matches: includes entire case with all tests
     * - If class name doesn't match: filters individual methods/functions
     * - If no methods match: excludes entire case
     *
     * @param FileDefinitions $file File with test case definitions
     * @param callable(FileDefinitions): CaseDefinitions $next Next interceptor in the chain
     *
     * @return CaseDefinitions Filtered test case definitions
     */
    public function locateTestCases(FileDefinitions $file, callable $next): CaseDefinitions
    {
        if ($this->skip) {
            return $next($file);
        }

        $definitions = $next($file);

        $result = [];
        foreach ($definitions->getCases() as $case) {
            $methods = [];
            # Filter by class name
            if ($case->reflection !== null) {
                $className = $case->reflection->getName();
                # Match class name
                foreach ([...$this->fqn, ...$this->fragment] as $name) {
                    if (self::has($name, $className)) {
                        $result[] = $case;
                        continue 2;
                    }
                }

                # Match methods
                foreach ($this->method as [$filterClass, $filterMethod]) {
                    # Skip if class name does not match
                    if (!self::has($filterClass, $className)) {
                        continue;
                    }

                    # Match method name
                    foreach ($case->tests->getTests() as $name => $test) {
                        $filterMethod === $test->reflection->getShortName() and $methods[$name] = $test;
                    }
                }

            }

            # Filter by function name
            foreach ($case->tests->getTests() as $name => $test) {
                foreach ([...$this->fqn, ...$this->fragment] as $f) {
                    if (self::has($f, $test->reflection->getName())) {
                        $methods[$name] = $test;
                        continue 2;
                    }
                }
            }

            # We have matched methods
            $methods === [] or $result[] = $case->with(tests: TestDefinitions::fromArray(...$methods));
        }

        return CaseDefinitions::fromArray(...$result);
    }

    /**
     * @return bool True if the needle is found as a whole word in the haystack, false otherwise.
     */
    private static function has(string $needle, string $haystack): bool
    {
        return \preg_match('/\\b' . \preg_quote($needle, '/') . '\\b$/', $haystack) === 1;
    }

    /**
     * Check if tokenized file contains any matching classes, functions, or methods.
     *
     * Performs quick matching against tokenized file data without loading full reflections.
     * Checks functions, classes, and methods in sequence, returning true on first match.
     *
     * @param TokenizedFile $file Tokenized file with extracted names
     *
     * @return bool True if any name matches, false otherwise
     */
    private function matchFile(TokenizedFile $file): bool
    {
        # Match functions
        foreach ($file->getFunctions() as $fqn) {
            foreach ([...$this->fqn, ...$this->fragment] as $name) {
                if (self::has($name, $fqn)) {
                    return true;
                }
            }
        }

        # Match classes
        foreach ($file->getClasses() as $class) {
            foreach ([...$this->fqn, ...$this->fragment] as $name) {
                if (self::has($name, $class)) {
                    return true;
                }
            }
        }

        # Match methods
        foreach ($file->getMethodsFQN() as $fqn) {
            # By fragment
            foreach ($this->fragment as $name) {
                if (self::has($name, $fqn)) {
                    return true;
                }
            }

            # By class and method name
            foreach ($this->method as [$className, $methodName]) {
                if (self::has($className . '::' . $methodName, $fqn)) {
                    return true;
                }
            }
        }

        return false;
    }
}
