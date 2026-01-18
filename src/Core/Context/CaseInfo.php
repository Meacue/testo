<?php

declare(strict_types=1);

namespace Testo\Core\Context;

use Testo\Core\Common\AttributedTrait;
use Testo\Core\Definition\CaseDefinition;
use Testo\Core\Internal\DefaultInvoker;
use Testo\Core\Value\CaseInstance;

/**
 * Information about run test case.
 */
final class CaseInfo
{
    use AttributedTrait;

    public readonly string $name;

    /**
     * Invoker closure for the test method.
     *
     * @var \Closure(TestInfo): mixed
     */
    public readonly \Closure $invoker;

    /**
     * @param array<non-empty-string, mixed> $attributes
     * @param callable(TestInfo): mixed $invoker Invoker for the test method.
     */
    public function __construct(
        public readonly CaseDefinition $definition,
        /**
         * Test Case class instance if class is defined, null otherwise.
         */
        public readonly ?CaseInstance $instance = null,
        array $attributes = [],
        callable $invoker = new DefaultInvoker(),
    ) {
        $this->name = $definition->getName();
        $this->attributes = $attributes;
        $this->invoker = $invoker(...);
    }

    public function with(
        ?\Closure $invoker = null,
    ): self {
        return new self(
            definition: $this->definition,
            instance: $this->instance,
            attributes: $this->attributes,
            invoker: $invoker ?? $this->invoker,
        );
    }

    /**
     * Replaces the case instance provider.
     */
    public function withInstance(?CaseInstance $instance): self
    {
        /** @see self::$instance */
        return $this->cloneWith('instance', $instance);
    }
}
