<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

final class ClassWithMethods
{
    public function publicMethod(): void {}

    protected function protectedMethod(): int
    {
        return 1;
    }

    private function privateMethod(string $arg): string
    {
        return $arg;
    }

    public static function staticMethod(): bool
    {
        return true;
    }

    public function __construct() {}

    public function __invoke(): void {}
}
