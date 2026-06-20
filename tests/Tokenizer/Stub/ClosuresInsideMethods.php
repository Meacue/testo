<?php

declare(strict_types=1);

namespace Tests\Tokenizer\Stub;

final class UsesClosures
{
    public function build(): \Closure
    {
        return static function (Covers $covers) {
            return Covers::class;
        };
    }

    public function map(array $items): array
    {
        return \array_map(function (Item $item): Item {
            return new Item($item);
        }, $items);
    }
}
