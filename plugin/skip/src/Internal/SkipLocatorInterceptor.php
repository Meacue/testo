<?php

declare(strict_types=1);

namespace Testo\Skip\Internal;

use Testo\Common\Reflection;
use Testo\Core\Definition\CaseDefinitions;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\CaseLocatorInterceptor;
use Testo\Skip;
use Testo\Tokenizer\Reflection\FileDefinitions;

/**
 * Flags the `#[Skip]`-annotated tests as {@see \Testo\Core\Definition\TestDefinition::$skipped}
 * once the cases of a file are located.
 *
 * The flag is what the rest of the pipeline reads: lifecycle hooks stay silent for a skipped test
 * and for a case without a test to run, and the core reports a skipped test even when nothing
 * else does. The reason travels separately, with the attribute, see {@see SkipInterceptor}.
 *
 * A class-level attribute is inherited from parents and traits, a method-level one from the
 * overridden method. Only {@see TestType::Test} cases are touched: `#[Skip]` is inert on
 * `#[Bench]`/`#[TestInline]` targets.
 *
 * @internal
 * @psalm-internal Testo\Skip
 */
#[InterceptorOptions(testType: TestType::Test)]
final readonly class SkipLocatorInterceptor implements CaseLocatorInterceptor
{
    #[\Override]
    public function locateTestCases(FileDefinitions $file, callable $next): CaseDefinitions
    {
        /** @var CaseDefinitions $result */
        $result = $next($file);

        foreach ($result->getCases() as $case) {
            if ($case->type !== TestType::Test->value) {
                continue;
            }

            $classSkipped = $case->reflection !== null
                && Reflection::fetchClassAttributes($case->reflection, attributeClass: Skip::class, limit: 1) !== [];

            foreach ($case->tests->getTests(active: null) as $test) {
                $classSkipped
                    || Reflection::fetchFunctionAttributes($test->reflection, attributeClass: Skip::class, limit: 1) !== []
                    and $test->skipped = true;
            }
        }

        return $result;
    }
}
