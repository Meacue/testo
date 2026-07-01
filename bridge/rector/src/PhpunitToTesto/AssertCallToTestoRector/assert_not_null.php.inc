<?php

class SomeTest
{
    public function test()
    {
        $this->assertNotNull($value);
    }
}
-----
<?php

class SomeTest
{
    public function test()
    {
        \Testo\Assert::notNull($value);
    }
}
