<?php

declare(strict_types=1);

namespace Testo\Core\Definition;

use Testo\Core\Context\TestInfo;

final class CaseDefinition
{
    public function __construct(
        public readonly ?string $name,

        /**
         * @var non-empty-string Type of the test case, e.g. 'test', 'unit', 'inline', 'bench', etc.
         */
        public readonly string $type,
        public readonly ?\ReflectionClass $reflection = null,
        public readonly TestDefinitions $tests = new TestDefinitions(),

        /**
         * @var null|\Closure(TestInfo): mixed Invoker for the test method.
         */
        public ?\Closure $invoker = null,
    ) {}

    public function with(
        ?string $name = null,
        ?TestDefinitions $tests = null,
    ): self {
        return new self(
            $name ?? $this->name,
            $this->type,
            $this->reflection,
            $tests ?? $this->tests,
            $this->invoker,
        );
    }

    public function getName(): string
    {
        return ($this->name ?? 'undefined') . " [{$this->type}]";
    }
}
