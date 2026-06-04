<?php

declare(strict_types=1);

namespace Tests\Core\Testing\Stub;

use Internal\Container\Container;
use Testo\Assert;
use Testo\Test;
use Testo\Testing\Attribute\Inject;

/**
 * Stub test case run through the whole Testo pipeline to verify that
 * {@see \Testo\Testing\Internal\InjectInterceptor} autowires {@see Inject}
 * properties of the instantiated test class.
 */
#[Test]
final class InjectFeatureStub
{
    #[Inject]
    private Container $container;

    public function injectedDependencyIsAvailable(): void
    {
        Assert::true(isset($this->container));
    }
}
