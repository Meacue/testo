<?php

declare(strict_types=1);

namespace Testo\Core\Internal;

use Testo\Core\Context\TestInfo;

/**
 * Default test invoker.
 */
final readonly class DefaultTestHandler
{
    public function __invoke(TestInfo $info): mixed
    {
        return $info->caseInfo->instance === null || $info->testDefinition->reflection->isStatic()
            ? $info->testDefinition->reflection->invoke(null, ...$info->arguments)
            : $info->testDefinition->reflection->invoke($info->caseInfo->instance->getInstance(), ...$info->arguments);
    }
}
