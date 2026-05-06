<?php

declare(strict_types=1);

namespace Testo\Inline\Internal;

use Testo\Assert;
use Testo\Core\Context\TestInfo;
use Testo\Inline\Internal\Exception\TestInlineAttributeMissingException;
use Testo\Inline\TestInline;

/**
 * @internal
 * @psalm-internal Testo\Inline
 */
final readonly class InlineHandler
{
    public function __invoke(TestInfo $info): mixed
    {
        $attr = $info->getAttribute(TestInline::class);
        $attr instanceof TestInline or throw TestInlineAttributeMissingException::fromTestInfo($info);

        # Execute the method or function
        $fn = $info->caseInfo->instance === null || $info->testDefinition->reflection->isStatic()
            ? $info->testDefinition->reflection->getClosure()
            : $info->testDefinition->reflection->getClosure($info->caseInfo->instance->getInstance());
        $result = $fn(...$info->arguments);

        # Verify the expected result
        if ($attr->result instanceof \Closure) {
            ($attr->result)($result);
            return $result;
        }

        Assert::same($result, $attr->result);
        return $result;
    }
}
