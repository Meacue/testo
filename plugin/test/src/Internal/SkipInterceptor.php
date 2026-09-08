<?php

declare(strict_types=1);

namespace Testo\Test\Internal;

use Psr\EventDispatcher\EventDispatcherInterface;
use Testo\Common\Reflection;
use Testo\Core\Context\CaseInfo;
use Testo\Core\Context\CaseResult;
use Testo\Core\Context\TestInfo;
use Testo\Core\Context\TestResult;
use Testo\Core\Definition\TestDefinition;
use Testo\Core\Exception\SkipTest;
use Testo\Core\Value\Status;
use Testo\Core\Value\Summary;
use Testo\Core\Value\TestType;
use Testo\Event\Test\TestPipelineFinished;
use Testo\Event\Test\TestPipelineStarting;
use Testo\Pipeline\Attribute\InterceptorOptions;
use Testo\Pipeline\Middleware\TestCaseRunInterceptor;
use Testo\Pipeline\Policy\ConflictPolicy;
use Testo\Test\Skip;
use Testo\Test\TestPlugin;

/**
 * Reports {@see Skip}-marked tests as skipped without running them.
 *
 * A case-level interceptor (registered by {@see TestPlugin}, also the attribute's fallback):
 * it deactivates every `#[Skip]`-marked test of the case before handing the case on — so lifecycle hooks
 * and the per-test pipeline never see them ({@see \Testo\Core\Definition\TestDefinitions::getTests()}
 * yields only the active tests) — and appends a synthetic {@see Status::Skipped} result for each
 * through the case's batch runner ({@see CaseInfo::withBatchRunner}). A runner already installed
 * by an outer interceptor (e.g. testo/fiber's) is wrapped, never replaced. Every synthetic result
 * is announced with the {@see TestPipelineStarting}/{@see TestPipelineFinished} pair, so reporters
 * render the skipped lines inside the case block; the core aggregates the case as usual.
 *
 * The case level, the cut-off before the hooks and the delivery after the pipeline handler are
 * the design settled in issue #313.
 *
 * Deactivation happens while the case runs, not while it is located, because a case whose active
 * test set is empty does not survive location: {@see \Testo\Application\Internal\SuiteFactory::create()}
 * drops it and returns `null` for a suite left without cases. A fully skipped case would
 * disappear that way, taking its `#[BeforeClass]`/`#[AfterClass]` hooks and the only place to
 * deliver its results with it. By run time the case and its {@see CaseInfo} already exist, which
 * makes this the one stage where the contract holds.
 *
 * Ordering: {@see InterceptorOptions::ORDER_DEFAULT} sits outer to the lifecycle interceptor
 * (so filtering happens before `#[BeforeClass]`) and inner to the fiber interceptor (so a
 * fiber batch runner is already on the case and gets wrapped).
 *
 * `testType: TestType::Test` keeps the interceptor off `#[Bench]` and `#[TestInline]` cases: their
 * finders (`BenchFinder`, `InlineFinder`) define the case with `prefill: false`, so it holds nothing
 * but their own members — a `#[Skip]` on one of them is inert, see {@see Skip}.
 *
 * Two deliberate consequences of delivering results this way:
 *
 * - The {@see \Testo\Event\Test\TestStarting}/{@see \Testo\Event\Test\TestFinished} pair is not
 *   emitted. Those announce a test body that begins and ends, and a skipped test has none; the
 *   TeamCity reporter covers the gap itself, emitting `testStarted` from
 *   {@see \Testo\Output\Teamcity\TeamcityPlugin::onTestPipelineFinished()} when the body never
 *   ran.
 * - Installing a batch runner takes the case off the core's inline path, which runs each test
 *   "without a runner/handler call frame so the stack stays shallow for deeply-recursive tests"
 *   ({@see \Testo\Application\Internal\Runner\CaseRunner::run()}). One `#[Skip]` in a case moves
 *   its remaining tests onto a handler frame.
 *
 * The flag is flipped once on the case's shared {@see \Testo\Core\Definition\TestDefinition}s, so a
 * second `runTestCase()` over the same {@see \Testo\Core\Definition\CaseDefinition} finds no skipped
 * tests left to report.
 *
 * Never throws for a skipped test — a throw from a case interceptor aborts the whole case.
 *
 * @internal
 * @psalm-internal Testo\Test
 */
#[InterceptorOptions(
    order: InterceptorOptions::ORDER_DEFAULT,
    # A class-level #[Skip] spawns a second instance through the fallback alias, next to the
    # one registered by TestPlugin; First collapses the duplicate onto the registered one.
    onConflict: ConflictPolicy::First,
    testType: TestType::Test,
)]
final readonly class SkipInterceptor implements TestCaseRunInterceptor
{
    /**
     * Takes no {@see Skip} parameter on purpose: the container also builds the instance for
     * the {@see TestPlugin} registration, where no attribute is at hand. The attributes are
     * looked up per case in {@see self::findSkipped()} instead.
     */
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    #[\Override]
    public function runTestCase(CaseInfo $info, callable $next): CaseResult
    {
        $skipped = $this->findSkipped($info);

        if ($skipped === []) {
            return $next($info);
        }

        # Deactivated, not discarded — the same way filtering narrows a case
        # (FilterInterceptor::locateTestCases()). The core runs only the active tests
        # (CaseRunner::run()), so the synthetic results below are their only delivery.
        foreach ($skipped as [$definition, $_]) {
            $definition->active = false;
        }

        # The case still runs (class-level hooks, events, the remaining tests): the skipped
        # results are appended by the batch runner inside the case window.
        $inner = $info->batchRunner;
        return $next($info->withBatchRunner(
            function (array $handlers) use ($inner, $info, $skipped): array {
                $results = $inner === null
                    ? \array_map(static fn(callable $handler): TestResult => $handler(), $handlers)
                    : $inner($handlers);

                foreach ($skipped as $name => [$definition, $attribute]) {
                    $results[] = $this->reportSkipped($info, $name, $definition, $attribute);
                }

                return $results;
            },
        ));
    }

    /**
     * `{testId} is skipped via #[Skip]`, extended with ` ==> {reason}` when a reason is given.
     * The generated part is always present, so reporters that render skip failure messages can
     * show that the skip came from `#[Skip]`. The test id is the test's address
     * ({@see \Testo\Core\Context\Identity\TestIdentity::fqn()}) — the exact string `--filter`
     * takes back.
     */
    private static function reason(TestInfo $info, Skip $attribute): string
    {
        $message = "{$info->identity->fqn()} is skipped via #[Skip]";

        return $attribute->reason === '' ? $message : "{$message} ==> {$attribute->reason}";
    }

    /**
     * Collects the skipped tests of the case: a method/function-level `#[Skip]` wins over the
     * class-level one; the class-level attribute is inherited from parents and traits.
     *
     * @return array<non-empty-string, array{TestDefinition, Skip}>
     */
    private function findSkipped(CaseInfo $info): array
    {
        $classAttribute = null;
        $reflection = $info->definition->reflection;
        if ($reflection !== null) {
            $attributes = Reflection::fetchClassAttributes($reflection, attributeClass: Skip::class, limit: 1);
            $attributes === [] or $classAttribute = $attributes[0]->newInstance();
        }

        $skipped = [];
        # Only the case's active tests: a non-test member (a helper, a lifecycle hook) carries no
        # skip semantics, and a test already deactivated by a filter is not part of this run —
        # reporting it as Skipped would resurrect what --filter/--group threw away.
        foreach ($info->definition->tests->getTests() as $name => $definition) {
            $attributes = Reflection::fetchFunctionAttributes(
                $definition->reflection,
                attributeClass: Skip::class,
                limit: 1,
            );
            $attribute = $attributes === [] ? $classAttribute : $attributes[0]->newInstance();

            $attribute === null or $skipped[$name] = [$definition, $attribute];
        }

        return $skipped;
    }

    /**
     * Builds the synthetic result for a skipped test and dispatches its pipeline events, so
     * reporters that render test lines from those events see the test as any other.
     */
    private function reportSkipped(
        CaseInfo $case,
        string $name,
        TestDefinition $definition,
        Skip $attribute,
    ): TestResult {
        $testInfo = (new TestInfo(name: $name, caseInfo: $case, testDefinition: $definition))
            ->withAttributes([Skip::class => [$attribute]]);

        $this->eventDispatcher->dispatch(new TestPipelineStarting($testInfo));

        $result = new TestResult(
            info: $testInfo,
            status: Status::Skipped,
            failure: new SkipTest(self::reason($testInfo, $attribute)),
            attributes: ['duration' => 0, 'description' => $definition->getDescription()],
            summary: Summary::forTest(Status::Skipped),
        );

        $this->eventDispatcher->dispatch(new TestPipelineFinished($testInfo, $result));

        return $result;
    }
}
