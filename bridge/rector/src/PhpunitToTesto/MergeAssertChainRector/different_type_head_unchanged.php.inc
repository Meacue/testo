<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::array($value)->isList();
        \Testo\Assert::string($value)->contains('x');
    }
}
