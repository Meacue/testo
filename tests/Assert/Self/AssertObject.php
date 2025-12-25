<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use stdClass;
use Testo\Assert;
use Testo\Attribute\Test;
use Testo\Expect;

/**
 * Assertion examples.
 */
final class AssertObject
{
    #[Test]
    public function instanceOf(): void
    {
        $obj = new \DateTimeImmutable();

        Assert::instanceOf(\DateTimeInterface::class, $obj);
        Assert::instanceOf(\DateTimeImmutable::class, $obj);

        Assert::object($obj)->instanceOf(\DateTimeInterface::class);
    }

    #[Test]
    public function hasProperty(): void
    {
        $obj = new stdClass();
        $obj->property = null;
        Assert::object($obj)->hasProperty('property');

        Expect::exception(Assert\State\Assertion\AssertionException::class);
        Assert::object($obj)->hasProperty('wrongPropertyName');
    }
}
