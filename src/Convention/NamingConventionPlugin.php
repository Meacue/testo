<?php

declare(strict_types=1);

namespace Testo\Convention;

use Testo\Common\Container;
use Testo\Common\PluginConfigurator;
use Testo\Convention\Internal\NamingConventionLocator;
use Testo\Pipeline\InterceptorCollector;

/**
 * Plugin that discovers test cases and tests by naming conventions.
 *
 * Locates files whose name ends with the configured case suffix (default `Test`),
 * then recognizes non-abstract classes sharing the same suffix as test cases
 * and public methods starting with the configured test prefix (default `test`) as tests.
 * Standalone functions matching the prefix are also discovered.
 *
 * Default convention (PHPUnit-compatible):
 *
 * ```
 *  // File: UserServiceTest.php
 *  final class UserServiceTest
 *  {
 *      public function testCreatesUser(): void { ... }
 *      public function testDeletesUser(): void { ... }
 *  }
 * ```
 *
 * Custom convention example:
 *
 * ```
 *  // testo.php plugins section
 *  new NamingConventionPlugin(caseSuffix: 'Spec', testPrefix: 'it'),
 *
 *  // File: UserServiceSpec.php
 *  final class UserServiceSpec
 *  {
 *      public function itCreatesUser(): void { ... }
 *  }
 * ```
 *
 * Standalone functions are supported as well:
 *
 * ```
 *  // File: validationTest.php
 *  function testEmailValidator(): void { ... }
 *  function testPasswordStrength(): void { ... }
 * ```
 *
 * @api
 */
final readonly class NamingConventionPlugin implements PluginConfigurator
{
    public function __construct(
        /**
         * Suffix for file names and class names to be recognized as test cases.
         * E.g. `Test` matches `UserServiceTest.php` and `class UserServiceTest`.
         */
        private string $caseSuffix = 'Test',

        /**
         * Prefix for method and function names to be recognized as tests.
         * Must be followed by a non-lowercase character (e.g. `testCreatesUser`).
         */
        private string $testPrefix = 'test',

        /**
         * When enabled, non-public methods matching the prefix are also discovered as tests.
         */
        private bool $allowPrivate = false,
    ) {
        # Validate Case Suffix
        \preg_match('/^[a-zA-Z0-9_ ]*$/', $this->caseSuffix) !== 1
        and throw new \InvalidArgumentException('Case suffix must be a valid PHP class name suffix.');

        # Validate Test Prefix
        \preg_match('/^[a-zA-Z_ ][a-zA-Z0-9_ ]*$/', $this->testPrefix) !== 1
        and throw new \InvalidArgumentException('Test prefix must be a valid PHP method name prefix.');
    }

    #[\Override]
    public function configure(Container $container): void
    {
        $container->get(InterceptorCollector::class)
            ->addInterceptor(new NamingConventionLocator($this->caseSuffix, $this->testPrefix, $this->allowPrivate));
    }
}
