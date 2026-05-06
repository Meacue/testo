<?php

declare(strict_types=1);

namespace Tests\Inline\Self;

use Testo\Assert;
use Testo\Inline\TestInline;

final class InstanceMethods
{
    #[TestInline(arguments: [1, 1], result: 2)]
    #[TestInline(arguments: [40, 2], result: 42)]
    public function publicSum(int $a, int $b): int
    {
        return $a + $b;
    }

    #[TestInline(arguments: [])]
    public function publicVoid(): void
    {
        Assert::true(true);
    }

    #[TestInline(arguments: ['foo', 'bar'], result: 'foo-bar')]
    #[TestInline(arguments: ['b' => 'foo', 'a' => 'bar'], result: 'bar-foo')]
    protected function protectedConcat(string $a, string $b): string
    {
        return $a . '-' . $b;
    }

    #[TestInline(arguments: [3, 4], result: 12)]
    #[TestInline(arguments: [-2, 5], result: -10)]
    private function privateMul(int $a, int $b): int
    {
        return $a * $b;
    }

    /**
     * Method body relies on $this — proves the instance is bound, not null.
     */
    #[TestInline(arguments: [10], result: 110)]
    public function dependsOnInstance(int $delta): int
    {
        return $this->base() + $delta;
    }

    private function base(): int
    {
        return 100;
    }
}
