<?php

declare(strict_types=1);

namespace Testo\Core\Context;

use Testo\Core\Value\Status;

/**
 * Result of running tests.
 *
 * @implements \IteratorAggregate<SuiteResult>
 */
final readonly class RunResult implements \IteratorAggregate
{
    public function __construct(
        /**
         * Test result collection.
         *
         * @var iterable<SuiteResult>
         */
        public iterable $results,
        public Status $status,
        /** Duration of the session in seconds. */
        public float $duration,
    ) {}

    /**
     * @return \Traversable<SuiteResult>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        yield from $this->results;
    }
}
