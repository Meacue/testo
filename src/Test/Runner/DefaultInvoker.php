<?php

declare(strict_types=1);

namespace Testo\Test\Runner;

use Testo\Test\Dto\TestInfo;

/**
 * Default test invoker.
 */
final class DefaultInvoker
{
    public function __invoke(TestInfo $info): mixed
    {
        return $info->caseInfo->instance === null
            ? $info->testDefinition->reflection->invoke(...$info->arguments)
            : $info->testDefinition->reflection->invoke($info->caseInfo->instance, ...$info->arguments);
    }
}
