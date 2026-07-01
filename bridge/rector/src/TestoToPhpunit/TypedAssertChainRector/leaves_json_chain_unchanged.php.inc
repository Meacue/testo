<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::json($s)->isObject();
    }
}
