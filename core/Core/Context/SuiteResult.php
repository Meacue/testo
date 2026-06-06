<?php

declare(strict_types=1);

namespace Testo\Core\Context;

use Testo\Core\Value\Status;
use Testo\Core\Value\Summary;

/**
 * Result of running tests.
 *
 * @implements \IteratorAggregate<CaseResult>
 * @api
 */
final readonly class SuiteResult implements \IteratorAggregate
{
    /**
     * @param iterable<CaseResult> $results Test result collection.
     * @param Summary $summary Aggregated statistics of the suite (sum of its case summaries).
     */
    public function __construct(
        public iterable $results,
        public Status $status,
        public Summary $summary = new Summary(),
    ) {}

    /**
     * @return \Traversable<CaseResult>
     */
    #[\Override]
    public function getIterator(): \Traversable
    {
        yield from $this->results;
    }

    /**
     * Counts tests by specific status across all cases in the suite.
     *
     * @deprecated Use {@see $summary}->count() instead.
     * @return int<0, max>
     */
    public function countTests(Status $status): int
    {
        return $this->summary->count($status);
    }

    /**
     * Counts the number of failed tests across all cases in the suite.
     *
     * @deprecated Use {@see $summary}->failed() instead.
     * @return int<0, max>
     */
    public function countFailedTests(): int
    {
        return $this->summary->failed();
    }

    /**
     * Counts the number of passed tests across all cases in the suite.
     *
     * @deprecated Use {@see $summary}->passed() instead.
     * @return int<0, max>
     */
    public function countPassedTests(): int
    {
        return $this->summary->passed();
    }
}
