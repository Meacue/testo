<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::array($list)->hasKeys('a');
        \Testo\Assert::array($list)->isList();
    }
}
-----
<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::array($list)->hasKeys('a')->isList();
    }
}
