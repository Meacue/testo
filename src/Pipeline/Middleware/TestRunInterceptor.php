<?php

declare(strict_types=1);

namespace Testo\Pipeline\Middleware;

use Testo\Application\Exception\PipelineFailure;
use Testo\Application\Internal\Runner\TestRunner;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Value\Status;
use Testo\Pipeline\Internal\InterceptorMarker;

/**
 * Interceptor for running tests.
 *
 * Exception Handling:
 * - Test exceptions are automatically caught by the {@see TestRunner} and placed in {@see TestResult}.
 *   To inspect test failures, check the returned {@see TestResult}, not try/catch.
 * - Interceptor exceptions halt the pipeline, wrapped in {@see PipelineFailure}, and placed
 *   in {@see TestResult} with {@see Status::Aborted} status.
 *   Use this only for critical errors (e.g., configuration issues).
 * - For cleanup after $next(), use try/finally since subsequent interceptors may throw.
 *
 * @extends InterceptorMarker<TestInfo, TestResult>
 */
interface TestRunInterceptor extends InterceptorMarker
{
    /**
     * @param TestInfo $info Information about the test to be run.
     * @param callable(TestInfo): TestResult $next Next interceptor or core logic to run the test.
     */
    public function runTest(TestInfo $info, callable $next): TestResult;
}
