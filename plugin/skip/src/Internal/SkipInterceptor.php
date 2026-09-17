<?php

declare(strict_types=1);

namespace Testo\Skip\Internal;

use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Exception\SkipTest;
use Testo\Core\Value\Status;
use Testo\Core\Value\Summary;
use Testo\Core\Value\TestType;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestRunInterceptor;
use Testo\Pipeline\Policy\ConflictPolicy;
use Testo\Skip;

/**
 * Reports a {@see Skip}-marked test as skipped, with the attribute's reason, instead of running it.
 *
 * Spawned by the attribute itself, one instance per `#[Skip]` found on the test's class and
 * function. {@see ConflictPolicy::Last} keeps the method-level one, which the attributes
 * interceptor lists after the class-level one, so the method reason wins over the class reason.
 *
 * Ordering: right after the filter, outer to everything that would engage for a test body: the
 * fiber wrap, data providers, `#[Retry]`/`#[Repeat]`, coverage, per-test lifecycle hooks. The core
 * dispatches `TestStarting`/`TestFinished` inner to all of them, so a skipped test gets only the
 * `TestPipelineStarting`/`TestPipelineFinished` pair. `testType: TestType::Test` keeps `#[Bench]`
 * and `#[TestInline]` cases out.
 *
 * Never throws: a throw is a runtime skip, and the `is skipped via #[Skip]` marker tells the two
 * apart in reports.
 *
 * @internal
 * @psalm-internal Testo\Skip
 */
#[InterceptorOptions(
    order: InterceptorOptions::ORDER_FILTER + 1_000,
    onConflict: ConflictPolicy::Last,
    testType: TestType::Test,
)]
final readonly class SkipInterceptor implements TestRunInterceptor
{
    public function __construct(
        private Skip $attribute,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        return new TestResult(
            info: $info,
            status: Status::Skipped,
            failure: new SkipTest($this->reason($info)),
            attributes: ['duration' => 0, 'description' => $info->testDefinition->getDescription()],
            summary: Summary::forTest(Status::Skipped),
        );
    }

    /**
     * `{testId} is skipped via #[Skip]`, extended with ` ==> {reason}` when a reason is given.
     * The test id is the string `--filter` takes back.
     */
    private function reason(TestInfo $info): string
    {
        $message = "{$info->identity->fqn()} is skipped via #[Skip]";

        return $this->attribute->reason === '' ? $message : "{$message} ==> {$this->attribute->reason}";
    }
}
