<?php

declare(strict_types=1);

namespace Testo\Core\Context;

use Testo\Core\Value\Status;
use Testo\Core\Value\Summary;

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
        /** Duration of the session in seconds (wall-clock). */
        public float $duration,
        /** Aggregated statistics of the session (sum of its suite summaries). */
        public Summary $summary = new Summary(),
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
