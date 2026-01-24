<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class CallStackAttribute
{
    public function __construct(
        public readonly string $label = '',
    ) {}
}
