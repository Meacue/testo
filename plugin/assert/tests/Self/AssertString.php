<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\Internal\Assertion\AssertString as AssertStringImpl;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::string()
 */
#[Test]
#[Covers(Assert::class, 'string')]
#[Covers(AssertStringImpl::class)]
final class AssertString
{
    public function checkStringDataType(): void
    {
        // This assertion checks incoming data type
        Assert::string("This is string");
        Assert::string("");
    }

    public function checkWrongDataType(): never
    {
        Expect::exception(AssertionException::class);
        Assert::string([666]);
    }

    public function contains(): never
    {
        Assert::string("What makes PHP the best programming language?")->contains("PHP the best");
        Assert::string("string")->contains("str");
        Assert::string("string")->contains(""); // works for empty strings too

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::string("What makes PHP the best programming language?")->contains("PHP is dying", 'my wonderful message');
    }

    public function notContains(): never
    {
        Assert::string("abcde")->contains("abc")->notContains('zxcv');
        Assert::string("string")->contains("str")->notContains('brr');

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::string("string")->notContains('str', 'my wonderful message');
    }
}
