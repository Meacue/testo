<?php

declare(strict_types=1);

namespace Testo\Core\Definition;

use Testo\Test;

/**
 * @api
 */
final readonly class TestDefinition
{
    public function __construct(
        public \ReflectionFunctionAbstract $reflection,
    ) {}

    public function getDescription(): ?string
    {
        // todo eliminate Test attribute dependency
        $attributes = $this->reflection->getAttributes(Test::class);
        if (\count($attributes) === 0) {
            return null;
        }

        /** @var \Testo\Test $testAttribute */
        $testAttribute = $attributes[0]->newInstance();
        return $testAttribute->description !== '' ? $testAttribute->description : null;
    }
}
