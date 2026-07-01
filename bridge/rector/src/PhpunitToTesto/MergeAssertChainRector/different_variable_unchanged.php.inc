<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::array($list)->isList();
        \Testo\Assert::array($other)->isList();
    }
}
