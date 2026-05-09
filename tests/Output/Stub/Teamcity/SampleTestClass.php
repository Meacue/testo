<?php

declare(strict_types=1);

namespace Tests\Output\Stub\Teamcity;

/**
 * Stub class used to back `\ReflectionMethod` for TeamCity logger tests.
 */
final class SampleTestClass
{
    public function passingTest(): void {}

    public function failingTest(): void {}
}
