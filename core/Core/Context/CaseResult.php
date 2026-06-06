<?php

declare(strict_types=1);

namespace Testo\Core\Context;

use Testo\Core\Value\Status;
use Testo\Core\Value\Summary;

/**
 * Result of running tests.
 *
 * @implements \IteratorAggregate<TestResult>
 * @api
 */
final readonly class CaseResult implements \IteratorAggregate
{
    /**
     * @param iterable<TestResult> $results Test result collection.
     * @param Summary $summary Aggregated statistics of the case (sum of its test summaries).
     */
    public function __construct(
        public iterable $results,
        public Status $status,
        public Summary $summary = new Summary(),
    ) {}

    #[\Override]
    public function getIterator(): \Traversable
    {
        yield from $this->results;
    }

    /**
     * Counts tests by specific status.
     *
     * @deprecated Use {@see $summary}->count() instead.
     * @return int<0, max>
     */
    public function countTests(Status $status): int
    {
        return $this->summary->count($status);
    }

    /**
     * Counts the number of failed tests.
     *
     * @deprecated Use {@see $summary}->failed() instead.
     * @return int<0, max>
     */
    public function countFailedTests(): int
    {
        return $this->summary->failed();
    }

    /**
     * Counts the number of passed tests.
     *
     * @deprecated Use {@see $summary}->passed() instead.
     * @return int<0, max>
     */
    public function countPassedTests(): int
    {
        return $this->summary->passed();
    }
}
