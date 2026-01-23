<?php

declare(strict_types=1);

namespace Testo\Application\Middleware\Locator;

use Testo\Core\Definition\CaseDefinitions;
use Testo\Pipeline\Middleware\CaseLocatorInterceptor;
use Testo\Pipeline\Middleware\FileLocatorInterceptor;
use Testo\Tokenizer\Reflection\FileDefinitions;
use Testo\Tokenizer\Reflection\TokenizedFile;

/**
 * Accepts files with the postfix "Test" and fetches test cases from them.
 *
 * E.g. "MyClassTest.php" will be accepted, while "MyClass.php" will not.
 * Then it will look for classes with the postfix "Test" inside the file.
 * If there are no such classes, it tries to find functions and considers them as test cases.
 *
 * Methods must be public and start with "test" prefix (e.g. testCreatesUser).
 * Functions must match the same pattern.
 *
 * Example class declaration:
 *
 * ```php
 *  final class UserServiceTest
 *  {
 *      public function testCreatesUser(): void { ... }
 *
 *      public function testDeletesUser(): void { ... }
 *  }
 * ```
 *
 * Example function declaration:
 *
 * ```php
 *  function testEmailValidator(): void { ... }
 *
 *  function testPasswordStrength(): void { ... }
 * ```
 */
final class FilePostfixTestLocatorInterceptor implements FileLocatorInterceptor, CaseLocatorInterceptor
{
    #[\Override]
    public function locateFile(TokenizedFile $file, callable $next): ?bool
    {
        return \str_ends_with($file->path->stem(), 'Test') ? true : $next($file);
    }

    #[\Override]
    public function locateTestCases(FileDefinitions $file, callable $next): CaseDefinitions
    {
        foreach ($file->classes as $class) {
            if (!$class->isAbstract() && \str_ends_with($class->getName(), 'Test')) {
                $case = $file->cases->define($class, $file);
                foreach ($class->getMethods() as $method) {
                    if ($method->isPublic() && \preg_match('/^test[^a-z]/', $method->getName()) === 1) {
                        $case->tests->define($method);
                    }
                }
            }
        }

        if ($file->functions === []) {
            return $next($file);
        }

        # Define a case for functions
        # Implement a lazy case definition
        $case = null;
        foreach ($file->functions as $function) {
            if (\preg_match('/^test[^a-z]/', $function->getShortName()) === 1) {
                $case ??= $file->cases->define(null, $file);
                $case->tests->define($function);
            }
        }

        return $next($file);
    }
}
