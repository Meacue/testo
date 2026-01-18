<?php

declare(strict_types=1);

namespace Testo\Core\Definition;

use Testo\Core\Context\TestInfo;

final class CaseDefinition
{
    /**
     * @param null|\Closure(TestInfo): mixed $invoker Invoker for the test method.
     */
    public function __construct(
        public readonly ?string $name,
        public readonly ?\ReflectionClass $reflection = null,
        public readonly TestDefinitions $tests = new TestDefinitions(),
        public ?\Closure $invoker = null,
    ) {}

    public function with(
        ?string $name = null,
        ?TestDefinitions $tests = null,
    ): self {
        return new self(
            $name ?? $this->name,
            $this->reflection,
            $tests ?? $this->tests,
            $this->invoker,
        );
    }

    public function getName(): string
    {
        return $this->name ?? 'undefined';
    }
}
