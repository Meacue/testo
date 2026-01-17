<?php

declare(strict_types=1);

namespace Testo\Test\Runner;

use Testo\Interceptor\Exception\TestCaseInstantiationException;
use Testo\Test\Dto\CaseInstance;

final class SimpleCaseInstantiator implements CaseInstance
{
    private ?object $instance = null;

    public function __construct(
        private readonly \ReflectionClass $reflection,
    ) {}

    #[\Override]
    public function getInstance(): object
    {
        return $this->instance ??= $this->createInstance();
    }

    #[\Override]
    public function hasInstance(): bool
    {
        return $this->instance !== null;
    }

    private function createInstance(): object
    {
        try {
            return $this->reflection->newInstance();
        } catch (\Throwable $e) {
            throw new TestCaseInstantiationException(previous: $e);
        }
    }
}
