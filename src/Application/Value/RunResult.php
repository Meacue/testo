<?php

declare(strict_types=1);

namespace Testo\Application\Value;

use Testo\Core\Context\SuiteResult;
use Testo\Core\Value\Status;

/**
 * Result of running tests.
 *
 * @implements \IteratorAggregate<SuiteResult>
 */
final class RunResult implements \IteratorAggregate
{
    public function __construct(
        /**
         * Test result collection.
         *
         * @var iterable<SuiteResult>
         */
        public readonly iterable $results,
        public readonly Status $status,
    ) {}

    /**
     * @return \Traversable<SuiteResult>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->results;
    }
}
