<?php

declare(strict_types=1);

namespace Testo\Bridge\Rector\Set;

/**
 * Typed handles for the conversion sets shipped under `config/sets/`.
 *
 * Reference these instead of hand-writing the set path in your `rector.php`:
 *
 * ```php
 * use Testo\Bridge\Rector\Set\TestoRectorSetList;
 *
 * return RectorConfig::configure()
 *     ->withPaths([__DIR__ . '/tests'])
 *     ->withSets([TestoRectorSetList::TESTO_TO_PHPUNIT]);
 * ```
 *
 * The constants are absolute paths to the set files, so they also work with
 * `$rectorConfig->import(...)`. Mirrors the {@see \Rector\PHPUnit\Set\PHPUnitSetList} convention.
 *
 * @api
 */
final class TestoRectorSetList
{
    /**
     * Testo -> PHPUnit. See {@see config/sets/testo-to-phpunit.php}.
     *
     * @var string
     */
    public const TESTO_TO_PHPUNIT = __DIR__ . '/../../config/sets/testo-to-phpunit.php';

    /**
     * PHPUnit -> Testo. See {@see config/sets/phpunit-to-testo.php}.
     *
     * @var string
     */
    public const PHPUNIT_TO_TESTO = __DIR__ . '/../../config/sets/phpunit-to-testo.php';

    /**
     * Pest -> Testo. See {@see config/sets/pest-to-testo.php}.
     *
     * @var string
     */
    public const PEST_TO_TESTO = __DIR__ . '/../../config/sets/pest-to-testo.php';
}
