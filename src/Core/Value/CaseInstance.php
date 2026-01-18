<?php

declare(strict_types=1);

namespace Testo\Core\Value;

/**
 * Exposes instance of the test case class.
 */
interface CaseInstance
{
    /**
     * Get instance of the test case class.
     *
     * Must not throw if {@see hasInstance()} returns true.
     *
     * @throws \RuntimeException If the instance cannot be created.
     */
    public function getInstance(): object;

    /**
     * Whether the instance of the test case class is created and available.
     *
     * @psalm-assert-if-true object $this->getInstance()
     */
    public function hasInstance(): bool;
}
