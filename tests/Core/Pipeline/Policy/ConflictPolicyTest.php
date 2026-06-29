<?php

declare(strict_types=1);

namespace Tests\Core\Pipeline\Policy;

use Testo\Assert;
use Testo\Codecov\Covers;
use Testo\Pipeline\Policy\ConflictPolicy;
use Testo\Test;

#[Test]
#[Covers(ConflictPolicy::class)]
final class ConflictPolicyTest
{
    public function defaultReturnsFirst(): void
    {
        $policy = ConflictPolicy::default();

        Assert::same($policy, ConflictPolicy::First);
    }

    public function exposesExpectedCases(): void
    {
        Assert::same(ConflictPolicy::cases(), [
            ConflictPolicy::First,
            ConflictPolicy::Merge,
            ConflictPolicy::Last,
            ConflictPolicy::Error,
        ]);
    }
}
