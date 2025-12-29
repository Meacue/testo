<?php

declare(strict_types=1);

namespace Testo\Sample\Internal;

use Psr\EventDispatcher\EventDispatcherInterface;
use Testo\Attribute\Test;
use Testo\Interceptor\CaseLocatorInterceptor;
use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Interceptor\Reflection\Reflection;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Module\Tokenizer\Reflection\FileDefinitions;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;
use Testo\Sample\MultipleResult;
use Testo\Sample\TestInline;
use Testo\Test\Dto\CaseDefinitions;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;
use Testo\Test\Event\Test\TestBatchFinished;
use Testo\Test\Event\Test\TestBatchStarting;
use Testo\Test\Event\Test\TestDataSetFinished;
use Testo\Test\Event\Test\TestDataSetStarting;

/**
 * Interceptor that runs the target method as a pure function with provided arguments and expected result.
 */
final class TestInlineInterceptor implements TestRunInterceptor
{
    /** @var callable(TestInfo): mixed Invoker for the test method. */
    private readonly \CLosure $invoker;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->invoker = (new InlineTestInvoker())(...);
    }

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        /** @var TestInline[] $attributes */
        $attributes = $info->getAttribute(TestInline::class);
        if ($attributes === []) {
            return $next($info);
        }

        if (\count($attributes) === 1) {
            $inline = \reset($attributes);
            return $next($info->with(arguments: $inline->arguments)->withAttribute(TestInline::class, $inline));
        }

        # Dispatch batch starting event
        $this->eventDispatcher->dispatch(new TestBatchStarting($info));

        // Run the test for each data set
        $results = [];
        $status = Status::Passed;
        foreach ($attributes as $index => $inline) {
            $newInfo = $info->with(arguments: $inline->arguments);
            $label = "$index";

            # Dispatch dataset starting event
            $this->eventDispatcher->dispatch(
                new TestDataSetStarting($newInfo, $label, $index)
            );

            try {
                $result = $next($newInfo);
            } catch (\Throwable $throwable) {
                $result = new TestResult(info: $newInfo, status: Status::Error, failure: $throwable);
            }

            # Dispatch dataset finished event
            $this->eventDispatcher->dispatch(
                new TestDataSetFinished($newInfo, $result, $label, $index)
            );

            unset($inline, $newInfo);
            $result->status->isFailure() and ($status = Status::Failed);
            $results[] = $result;
        }

        $results = new MultipleResult($results);

        $finalResult = new TestResult(info: $info, status: $status, result: $results, attributes: [
            MultipleResult::class => $results,
        ]);

        # Dispatch batch finished event
        $this->eventDispatcher->dispatch(new TestBatchFinished($info, $finalResult));

        return $finalResult;
    }
}
