<?php

// Flat facade calls are `void` static calls with no chain to merge onto — left as separate lines.
class SomeTest
{
    public function test()
    {
        \Testo\Assert::count($list, 2);
        \Testo\Assert::contains($list, 'a');
    }
}
