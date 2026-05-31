<?php

declare(strict_types=1);

namespace Testo\Core\Log;

/**
 * A single message recorded during a test run.
 *
 * A message carries a timestamp, the logical channel it belongs to (e.g. `stdout`, `sql-log`),
 * a severity {@see Level}, its content and an optional structured context. The content is always
 * the displayable text (already interpolated for logger-produced messages); the context keeps the
 * raw PSR-3 context array for structured consumers. Messages are produced by output capturing and
 * by any other producer that writes into a named channel — test code, middleware, or plugins.
 *
 * @psalm-immutable
 * @api
 */
final readonly class Message
{
    /**
     * @param float $time Wall-clock timestamp the message was recorded at, as returned by {@see \microtime()}.
     * @param non-empty-string $channel Logical stream the message belongs to (e.g. `stdout`, `sql-log`).
     * @param Level $level Severity of the message.
     * @param non-empty-string $content Displayable message payload.
     * @param array<string, mixed> $context Structured context (e.g. PSR-3 log context); empty for raw output.
     */
    public function __construct(
        public float $time,
        public string $channel,
        public Level $level,
        public string $content,
        public array $context = [],
    ) {}
}
