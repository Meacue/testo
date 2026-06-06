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
    /**
     * @param iterable<SuiteResult> $results Test result collection.
     * @param float $duration Duration of the session in seconds (wall-clock).
     * @param Summary $summary Aggregated statistics of the session (sum of its suite summaries).
     */
    public function __construct(
        public iterable $results,
        public Status $status,
        public float $duration,
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
