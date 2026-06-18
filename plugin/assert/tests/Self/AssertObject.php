<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Assert\Internal\Assertion\AssertObject as AssertObjectImpl;
use Testo\Assert\State\Assertion\AssertionException;
use Testo\Codecov\Covers;
use Testo\Expect;
use Testo\Test;

/**
 * @see Assert::object()
 */
#[Test]
#[Covers(Assert::class, 'object')]
#[Covers(Assert::class, 'instanceOf')]
#[Covers(AssertObjectImpl::class)]
final class AssertObject
{
    public function instanceOf(): never
    {
        $obj = new \DateTimeImmutable();

        Assert::instanceOf($obj, \DateTimeInterface::class);
        Assert::instanceOf($obj, \DateTimeImmutable::class);
        Assert::object($obj)->instanceOf(\DateTimeInterface::class);

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::object($obj)->instanceOf(\Throwable::class, 'my wonderful message');
    }

    public function hasProperty(): never
    {
        $obj = new class {
            private int $private = 42;
            public int $public = 42;
        };

        Assert::object($obj)->hasProperty('private');
        Assert::object($obj)->hasProperty('public');

        Expect::exception(AssertionException::class)
            ->withMessageContaining('my wonderful message');
        Assert::object($obj)->hasProperty('wrongPropertyName', 'my wonderful message');
    }
}
