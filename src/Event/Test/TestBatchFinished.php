<?php

declare(strict_types=1);

namespace Testo\Event\Test;

/**
 * Event triggered when a batch of test runs has finished.
 *
 * This event signals renderers to close the nesting level and display the aggregated result
 * of all test runs within the batch.
 *
 * @psalm-immutable
 * @api
 */
final readonly class TestBatchFinished extends TestResultEvent {}
