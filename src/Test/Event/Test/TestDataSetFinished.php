<?php

declare(strict_types=1);

namespace Testo\Test\Event\Test;

use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Event triggered when a DataProvider dataset execution finishes.
 *
 * This event fires for each individual dataset run within a DataProvider test,
 * providing the result of that specific dataset execution.
 *
 * @psalm-immutable
 */
final class TestDataSetFinished extends TestResultEvent
{
    public function __construct(
        TestInfo $testInfo,
        TestResult $testResult,

        /**
         * The key from the DataProvider (from yield key).
         *
         * @var string|int
         */
        public readonly string|int $datasetKey,

        /**
         * The zero-based index of the DataProvider attribute in case of multiple providers.
         *
         * @var null|int<0, max>
         */
        public readonly ?int $providerIndex,

        /**
         * The zero-based index of this dataset in the sequence.
         *
         * @var int<0, max>
         */
        public readonly int $datasetIndex,
    ) {
        parent::__construct($testInfo, $testResult);
    }
}
