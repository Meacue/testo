<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::array($log->all())->isList();
        \Testo\Assert::array($log->all())->hasKeys('a');
    }
}
