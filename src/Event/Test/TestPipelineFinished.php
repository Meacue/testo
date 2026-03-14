<?php

declare(strict_types=1);

namespace Testo\Event\Test;

/**
 * Event triggered after the test pipeline (run interceptors) has finished executing.
 *
 * This is the last event in the test lifecycle, fired after all run interceptors have completed.
 *
 * @psalm-immutable
 * @api
 */
final readonly class TestPipelineFinished extends TestResultEvent {}
