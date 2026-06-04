<?php

declare(strict_types=1);

namespace Testo\Testing\Internal;

use Testo\Core\Value\CaseInstance;

/**
 * Decorates a {@see CaseInstance} provider and autowires the test case object's
 * {@see \Testo\Testing\Attribute\Inject} properties the first time it is created.
 *
 * Injection happens lazily on {@see getInstance()} so it runs before any test or
 * lifecycle method touches the instance, regardless of interceptor ordering.
 *
 * @internal
 * @psalm-internal Testo\Testing
 */
final class InjectingCaseInstance implements CaseInstance
{
    private bool $injected = false;

    public function __construct(
        private readonly CaseInstance $instance,
        private readonly PropertyInjector $injector,
    ) {}

    #[\Override]
    public function getInstance(): object
    {
        $instance = $this->instance->getInstance();

        if (!$this->injected) {
            $this->injected = true;
            $this->injector->inject($instance);
        }

        return $instance;
    }

    #[\Override]
    public function hasInstance(): bool
    {
        return $this->instance->hasInstance();
    }
}
