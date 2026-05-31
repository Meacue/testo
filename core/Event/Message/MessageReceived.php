<?php

declare(strict_types=1);

namespace Testo\Event\Message;

use Testo\Core\Log\Message;

/**
 * Event fired the moment a message is recorded during a run.
 *
 * Dispatched in real time — for output captured from the buffer this means the event fires per
 * flushed chunk, not once at the end of the test. Listeners can stream the content somewhere
 * (console, file, report) as it arrives.
 *
 * @psalm-immutable
 * @api
 */
final readonly class MessageReceived
{
    public function __construct(
        public Message $message,
    ) {}
}
