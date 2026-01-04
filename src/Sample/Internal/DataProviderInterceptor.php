<?php

declare(strict_types=1);

namespace Testo\Sample\Internal;

use Psr\EventDispatcher\EventDispatcherInterface;
use Testo\Interceptor\Locator\DataPointer;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Module\Interceptor\InterceptorOptions;
use Testo\Module\Interceptor\Policy\ConflictPolicy;
use Testo\Sample\DataProvider;
use Testo\Sample\MultipleResult;
use Testo\Test\Dto\Status;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;
use Testo\Test\Event\Test\TestBatchFinished;
use Testo\Test\Event\Test\TestBatchStarting;
use Testo\Test\Event\Test\TestDataSetFinished;
use Testo\Test\Event\Test\TestDataSetStarting;

/**
 * Interceptor that handles data providers for tests.
 */
#[InterceptorOptions(order: -20_000, onConflict: ConflictPolicy::First)]
final class DataProviderInterceptor implements TestRunInterceptor
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        // Dispatch batch starting event
        $this->eventDispatcher->dispatch(new TestBatchStarting($info));

        $results = [];
        $status = Status::Passed;
        try {
            # Collect attributes
            $attributes = $info->testDefinition->reflection
                ->getAttributes(DataProviderAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);

            # Check Filters
            $dataPointer = $info->getAttribute(DataPointer::class);

            foreach ($attributes as $i => $attribute) {
                if ($dataPointer !== null && $dataPointer->provider !== $i) {
                    continue;
                }

                $attr = $attribute->newInstance();

                # Handle DataProvider attributes
                if ($attr instanceof DataProvider) {
                    foreach ($this->handleDataProvider($info, $next, $attr, $dataPointer) as $result) {
                        $result->status->isFailure() and $status = Status::Failed;
                        $results[] = $result;
                    }
                }
            }
        } catch (\Throwable $e) {
            $status = Status::Error;
            throw $e;
        } finally {
            $summary = new MultipleResult($results);

            $finalResult = new TestResult(
                info: $info,
                status: $status,
                result: $summary,
                attributes: [
                    MultipleResult::class => $summary,
                ],
            );

            // Dispatch batch finished event
            $this->eventDispatcher->dispatch(new TestBatchFinished($info, $finalResult));
        }

        return $finalResult;
    }

    /**
     * @param callable(TestInfo): TestResult $next Next interceptor or core logic to run the test.
     * @return array<array-key, TestResult>
     */
    private function handleDataProvider(TestInfo $info, callable $next, DataProvider $attribute, ?DataPointer $pointer): array
    {
        $provider = $attribute->provider;

        # String provider definition means the method name in the test class
        $ref = $info->testDefinition->reflection;
        if (\is_string($provider) && $ref instanceof \ReflectionMethod) {
            /** @var \ReflectionClass $class */
            $class = $ref->getDeclaringClass();

            if ($class->hasMethod($provider)) {
                $m = $class->getMethod($provider);
                $provider = match (true) {
                    $m->isStatic() => $m->getClosure(null),
                    default => static fn() => $m->getClosure($info->caseInfo->instance),
                };
            }

            \is_callable($provider) or throw new \InvalidArgumentException(
                'DataProvider provider must be a callable or method name string.',
            );
        }

        # Fetch data sets from the provider
        $dataSets = $provider();
        \is_iterable($dataSets) or throw new \InvalidArgumentException(
            'Data provider must return an iterable of data sets.',
        );

        # Run the test for each data set
        $results = [];
        $num = 0;
        foreach ($dataSets as $k => $dataset) {
            if ($pointer !== null && $pointer->dataset !== null && $pointer->dataset !== $num) {
                ++$num;
                continue;
            }

            \is_array($dataset) or throw new \InvalidArgumentException('Each data set must be an array of arguments.');

            # Determine unique label for the data set
            $label = (string) $k;
            ++$num;
            $i = 0;
            while (\array_key_exists($label, $results)) {
                ++$i;
                $label = "$k~$i";
            }

            $newInfo = $info->with(
                arguments: $dataset,
            );

            // Dispatch dataset starting event
            $this->eventDispatcher->dispatch(new TestDataSetStarting($newInfo, $label, $num - 1));

            try {
                $result = $next($newInfo);
            } catch (\Throwable $throwable) {
                $result = new TestResult(
                    info: $newInfo,
                    status: Status::Error,
                    failure: $throwable,
                );
            }

            // Dispatch dataset finished event
            $result->withAttribute(DataPointer::class, $pointer);
            $this->eventDispatcher->dispatch(new TestDataSetFinished($newInfo, $result, $label, $num - 1));

            unset($dataset, $newInfo);
            $results[$label] = $result;
        }

        return $results;
    }
}
