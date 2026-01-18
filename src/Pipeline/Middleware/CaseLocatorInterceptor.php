<?php

declare(strict_types=1);

namespace Testo\Pipeline\Middleware;

use Testo\Core\Definition\CaseDefinitions;
use Testo\Pipeline\Internal\InterceptorMarker;
use Testo\Tokenizer\Reflection\FileDefinitions;

/**
 * Intercept locating test files and test cases.TokenizedFile
 *
 * @extends InterceptorMarker<FileDefinitions, CaseDefinitions>
 */
interface CaseLocatorInterceptor extends InterceptorMarker
{
    /**
     * Locate test cases in the given file.
     *
     * Class and function reflections are available there.
     *
     * @param FileDefinitions $file File to locate test cases in.
     * @param callable(FileDefinitions): CaseDefinitions $next Next interceptor or core logic to locate test cases.
     */
    public function locateTestCases(FileDefinitions $file, callable $next): CaseDefinitions;
}
