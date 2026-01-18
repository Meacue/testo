<?php

declare(strict_types=1);

namespace Testo\Pipeline\Middleware;

use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\CaseResult;
use Testo\Core\Context\SuiteInfo;
use Testo\Core\Context\SuiteResult;
use Testo\Pipeline\Internal\InterceptorMarker;

/**
 * Intercept running a test suite.
 *
 * @extends InterceptorMarker<CaseInfo, CaseResult>
 */
interface TestSuiteRunInterceptor extends InterceptorMarker
{
    /**
     * @param SuiteInfo $info Test suite to run.
     * @param callable(SuiteInfo): SuiteResult $next Next interceptor or core logic to run the test suite.
     */
    public function runTestSuite(SuiteInfo $info, callable $next): SuiteResult;
}
