<?php

declare(strict_types=1);

namespace Tests\Convention\Unit;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Convention\NamingConventionPlugin;
use Testo\Data\DataSet;
use Testo\Expect;
use Testo\Test;

#[Test]
#[Covers(NamingConventionPlugin::class)]
final class NamingConventionPluginTest
{
    /**
     * Valid configurations construct without throwing. An empty case suffix matches every file
     * and is intentionally permitted.
     *
     * @param string $caseSuffix
     * @param non-empty-string $testPrefix
     */
    #[DataSet(['Test', 'test'], 'defaults')]
    #[DataSet(['Spec', 'test'], 'custom suffix')]
    #[DataSet(['Test', 'it'], 'custom prefix')]
    #[DataSet(['', 'test'], 'empty suffix')]
    public function validConfigurationIsAccepted(string $caseSuffix, string $testPrefix): void
    {
        $plugin = new NamingConventionPlugin(caseSuffix: $caseSuffix, testPrefix: $testPrefix);

        Assert::instanceOf($plugin, NamingConventionPlugin::class);
    }

    /**
     * @param non-empty-string $caseSuffix
     */
    #[DataSet(['Foo-Bar'], 'dash')]
    #[DataSet(['Foo$'], 'special char')]
    public function invalidCaseSuffixThrows(string $caseSuffix): never
    {
        Expect::exception(\InvalidArgumentException::class)
            ->withMessage('Case suffix must be a valid PHP class name suffix.');

        new NamingConventionPlugin(caseSuffix: $caseSuffix);
    }

    /**
     * @param string $testPrefix
     */
    #[DataSet([''], 'empty')]
    #[DataSet(['1foo'], 'starts with digit')]
    #[DataSet(['it-does'], 'dash')]
    public function invalidTestPrefixThrows(string $testPrefix): never
    {
        Expect::exception(\InvalidArgumentException::class)
            ->withMessage('Test prefix must be a valid PHP method name prefix.');

        new NamingConventionPlugin(testPrefix: $testPrefix);
    }
}
