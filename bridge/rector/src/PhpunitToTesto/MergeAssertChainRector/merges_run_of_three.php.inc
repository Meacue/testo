<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::string($name)->contains('foo');
        \Testo\Assert::string($name)->notContains('bar');
        \Testo\Assert::string($name)->contains('baz');
    }
}
-----
<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::string($name)->contains('foo')->notContains('bar')->contains('baz');
    }
}
