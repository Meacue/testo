<?php

declare(strict_types=1);

namespace Tests\Output\Stub\Teamcity;

/**
 * Abstract base whose test method is inherited by a concrete subclass, used to back the TeamCity
 * logger's inherited-test location-hint test.
 */
abstract class AbstractSampleTestCase
{
    public function inheritedTest(): void {}
}
