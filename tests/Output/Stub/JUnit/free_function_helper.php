<?php

declare(strict_types=1);

namespace Tests\Output\Stub\JUnit;

if (!\function_exists(__NAMESPACE__ . '\\junitFreeFunction')) {
    function junitFreeFunction(): void {}
}
