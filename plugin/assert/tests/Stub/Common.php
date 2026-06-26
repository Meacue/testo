<?php

declare(strict_types=1);

namespace Tests\Assert\Stub;

use Testo\Assert;
use Testo\Assert\ExpectNoAssertions;
use Testo\Assert\ExpectException;
use Testo\Test;

final class Common
{
    #[Test]
    public function risky(): void
    {
        // No assertions here
    }

    #[Test]
    #[ExpectNoAssertions]
    public function noAssertionsAllowed(): void
    {
        // No assertions here, but the attribute keeps the test Passed
    }

    #[Test]
    #[ExpectNoAssertions]
    public function assertsDespiteAttribute(): void
    {
        // The attribute declares "no assertions", yet the test asserts — that contradiction is Risky
        Assert::same(1, 1);
    }

    #[Test]
    #[ExpectException(\RuntimeException::class)]
    #[ExpectNoAssertions]
    public function expectsExceptionDespiteAttribute(): never
    {
        // Expecting an exception is itself an assertion, so it contradicts the attribute — Risky
        throw new \RuntimeException('boom');
    }
}
