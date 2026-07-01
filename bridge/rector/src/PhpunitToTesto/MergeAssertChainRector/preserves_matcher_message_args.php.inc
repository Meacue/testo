<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::string($name)->contains('foo', 'must contain foo');
        \Testo\Assert::string($name)->notContains('bar', 'must not contain bar');
    }
}
-----
<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::string($name)->contains('foo', 'must contain foo')->notContains('bar', 'must not contain bar');
    }
}
