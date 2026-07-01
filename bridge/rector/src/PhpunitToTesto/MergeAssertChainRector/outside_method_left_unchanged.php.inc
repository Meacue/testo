<?php

// Outside a class (free function): chains are only merged inside a test method.
function helper()
{
    \Testo\Assert::array($list)->hasKeys('a');
    \Testo\Assert::array($list)->isList();
}
