<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::array($list)->hasKeys('a');
        $list[] = 'b';
        \Testo\Assert::array($list)->isList();
    }
}
