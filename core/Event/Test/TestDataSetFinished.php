<?php

declare(strict_types=1);

namespace Testo\Event\Test;

use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;

/**
 * Event triggered when a DataProvider dataset execution finishes.
 *
 * This event fires for each individual dataset run within a DataProvider test,
 * providing the result of that specific dataset execution.
 *
 * @psalm-immutable
 * @api
 */
final readonly class TestDataSetFinished extends TestResultEvent
{
    public function __construct(
        TestInfo $testInfo,
        TestResult $testResult,

        /**
         * The key from the DataProvider (from yield key).
         *
         * @var string|int
         */
        public string|int $datasetKey,

        /**
         * The zero-based index of the DataProvider attribute in case of multiple providers.
         *
         * @var null|int<0, max>
         */
        public ?int $providerIndex,

        /**
         * The zero-based index of this dataset in the sequence.
         *
         * @var int<0, max>
         */
        public int $datasetIndex,
    ) {
        parent::__construct($testInfo, $testResult);
    }
}
