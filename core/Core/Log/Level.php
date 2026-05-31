<?php

declare(strict_types=1);

namespace Testo\Core\Log;

/**
 * Severity level of a {@see Message}.
 *
 * Cases and their string values mirror the eight PSR-3 (`Psr\Log\LogLevel`) levels, so a message
 * level maps to/from any PSR logger without a hard dependency on `psr/log`: `$message->level->value`
 * yields the PSR-3 string, and {@see Level::from()} reverses it.
 *
 * @api
 */
enum Level: string
{
    case Emergency = 'emergency';
    case Alert = 'alert';
    case Critical = 'critical';
    case Error = 'error';
    case Warning = 'warning';
    case Notice = 'notice';
    case Info = 'info';
    case Debug = 'debug';
}
