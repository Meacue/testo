<?php

declare(strict_types=1);

namespace Testo\Assert\Internal\Expectation;

use Testo\Assert\State\ExpectLeaksFailure;
use Testo\Assert\State\Success;
use Testo\Assert\TestState;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestResult;

/**
 * Asserts that objects are leaked (not garbage collected).
 *
 * @see Expect::leaks()
 * @internal
 */
final class Leaks
{
    /** @var array<array-key, array{0: class-string, 1: \WeakReference}> */
    private readonly array $map;

    private string $message = '';

    public function __construct(
        object ...$objects,
    ) {
        $this->map = \array_map(static fn(object $object): array => [
            $object::class,
            \WeakReference::create($object),
        ], $objects);
    }

    /**
     * Set an optional description for the expectation.
     */
    public function message(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function __invoke(TestResult $result, TestState $state): TestResult
    {
        /** @var array<array-key, class-string> $r */
        $r = [];
        foreach ($this->map as $item) {
            if ($item[1]->get() === null) {
                $r[] = $item[0];
            }
        }

        if ($r === []) {
            $state->history[] = new Success(
                \sprintf(
                    '%d object%s cached in memory.',
                    \count($this->map),
                    \count($this->map) === 1 ? '' : 's',
                ),
                $this->message,
            );
            return $result;
        }

        $e = ExpectLeaksFailure::fromClassArray(
            $r,
            $this->message,
        );
        $state->history[] = $e;

        return $result->with(status: Status::Failed)->withFailure($e);
    }
}
