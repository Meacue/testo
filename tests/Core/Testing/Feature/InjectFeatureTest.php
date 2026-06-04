<?php

declare(strict_types=1);

namespace Tests\Core\Testing\Feature;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Core\Value\Status;
use Testo\Test;
use Testo\Testing\Attribute\TestingSuite;
use Testo\Testing\Helper\TestRunner;
use Testo\Testing\InjectPlugin;
use Testo\Testing\Internal\InjectInterceptor;
use Tests\Core\Testing\Stub\InjectFeatureStub;

/**
 * End-to-end check that {@see InjectInterceptor} autowires {@see \Testo\Testing\Attribute\Inject}
 * properties of a test case instance when it is created during a real pipeline run.
 */
#[Test]
#[Covers(InjectInterceptor::class)]
#[Covers(InjectPlugin::class)]
#[TestingSuite(
    path: __DIR__ . '/../Stub/InjectFeatureStub.php',
    plugins: [InjectPlugin::class],
)]
final class InjectFeatureTest
{
    public function dependencyIsInjectedIntoTestCaseInstance(): void
    {
        $result = TestRunner::runTest([InjectFeatureStub::class, 'injectedDependencyIsAvailable']);

        Assert::same($result->status, Status::Passed);
    }
}
