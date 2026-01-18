<?php

declare(strict_types=1);

namespace Testo\Pipeline\Middleware;

use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\CaseResult;
use Testo\Pipeline\Internal\InterceptorMarker;

/**
 * Intercept running a test case.
 *
 * @extends InterceptorMarker<CaseInfo, CaseResult>
 */
interface TestCaseRunInterceptor extends InterceptorMarker
{
    /**
     * @param CaseInfo $info Test case to run.
     * @param callable(CaseInfo): CaseResult $next Next interceptor or core logic to run the test case.
     */
    public function runTestCase(CaseInfo $info, callable $next): CaseResult;
}
