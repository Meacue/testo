<?php

declare(strict_types=1);

namespace Tests\Assert\Self;

use Testo\Assert;
use Testo\Attribute\ExpectException;
use Testo\Attribute\Test;
use Testo\Expect;

/**
 * @see Expect::exception()
 */
final class ExpectExceptionTest
{
    #[Test]
    public function messageAndCode(): never
    {
        Expect::exception(\LogicException::class)
            ->withMessage('This is a logic exception')
            ->withCode(123)
            ->withCode([320, 123, 456])
            ->fromMethod(self::class, __FUNCTION__)
            ->withNoPrevious();

        throw new \LogicException('This is a logic exception', 123);
    }

    #[Test]
    #[ExpectException(Assert\State\ExpectLeaksFailure::class)]
    public function previous(): void
    {
        Expect::exception(\Throwable::class)
            ->withPrevious(
                \RuntimeException::class,
                static fn(Assert\Expectation\ExpectedException $ex) => $ex
                    ->withCode(456)
                    ->withMessage('Previous exception')
                    ->withNoPrevious(),
            );

        $previous = new \RuntimeException('Previous exception', 456);
        throw new \LogicException('This is a logic exception', 123, $previous);
    }
}
