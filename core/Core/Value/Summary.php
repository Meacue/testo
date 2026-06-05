<?php

declare(strict_types=1);

namespace Testo\Core\Value;

/**
 * Aggregated statistics of a test run at any level of the hierarchy
 * (Test → Case → Suite → Run).
 *
 * Test counts are keyed by {@see Status} name and summed on {@see self::merge()}, so the same DTO
 * describes a single test and a whole session. The {@see $metrics} map carries plugin-contributed
 * counters (e.g. the Assert plugin records the number of assertions under the `assertions` key),
 * which keeps the core unaware of any specific plugin while still letting their numbers bubble up.
 *
 * @api
 */
final readonly class Summary
{
    /**
     * @param array<non-empty-string, int<0, max>> $counts Test counts keyed by {@see Status::name}.
     * @param array<non-empty-string, int<0, max>> $metrics Plugin-contributed counters, e.g. `assertions`.
     * @param float $duration Accumulated test duration in seconds.
     */
    public function __construct(
        public array $counts = [],
        public array $metrics = [],
        public float $duration = 0.0,
    ) {}

    /**
     * Summary of a single executed test.
     *
     * @param float $duration Test duration in seconds.
     */
    public static function forTest(Status $status, float $duration = 0.0): self
    {
        return new self([$status->name => 1], [], $duration);
    }

    /**
     * Sums the given summaries into one.
     *
     * @param iterable<self> $summaries
     */
    public static function combine(iterable $summaries): self
    {
        $result = new self();
        foreach ($summaries as $summary) {
            $result = $result->merge($summary);
        }

        return $result;
    }

    /**
     * Total number of tests across all statuses.
     *
     * @return int<0, max>
     */
    public function total(): int
    {
        return \max(0, \array_sum($this->counts));
    }

    /**
     * Number of tests with the given status.
     *
     * @return int<0, max>
     */
    public function count(Status $status): int
    {
        return $this->counts[$status->name] ?? 0;
    }

    /**
     * Number of failed tests ({@see Status::isFailure()}).
     *
     * @return int<0, max>
     */
    public function failed(): int
    {
        return $this->count(Status::Failed) + $this->count(Status::Error);
    }

    /**
     * Number of successful tests ({@see Status::isSuccessful()}).
     *
     * @return int<0, max>
     */
    public function passed(): int
    {
        return $this->count(Status::Passed) + $this->count(Status::Flaky);
    }

    /**
     * Value of a plugin-contributed metric, or `0` when it was never recorded.
     *
     * @param non-empty-string $name
     * @return int<0, max>
     */
    public function metric(string $name): int
    {
        return $this->metrics[$name] ?? 0;
    }

    /**
     * Returns a copy with all counts, metrics and duration of {@see $other} added on top.
     */
    public function merge(self $other): self
    {
        $counts = $this->counts;
        foreach ($other->counts as $name => $value) {
            $counts[$name] = ($counts[$name] ?? 0) + $value;
        }

        $metrics = $this->metrics;
        foreach ($other->metrics as $name => $value) {
            $metrics[$name] = ($metrics[$name] ?? 0) + $value;
        }

        return new self($counts, $metrics, $this->duration + $other->duration);
    }

    /**
     * Returns a copy with the given metric increased by {@see $value} (created if absent).
     *
     * @param non-empty-string $name
     * @param int<0, max> $value
     */
    public function withAddedMetric(string $name, int $value): self
    {
        $metrics = $this->metrics;
        $metrics[$name] = ($metrics[$name] ?? 0) + $value;

        return new self($this->counts, $metrics, $this->duration);
    }

    /**
     * Returns a copy counting a single test of the given status, preserving metrics and duration.
     *
     * Used to stamp the final status onto an atomic test's summary as late as possible, because a
     * test status may still change (e.g. {@see \Testo\Assert} expectations or retries) after the
     * summary was created.
     */
    public function withStatus(Status $status): self
    {
        return new self([$status->name => 1], $this->metrics, $this->duration);
    }
}
