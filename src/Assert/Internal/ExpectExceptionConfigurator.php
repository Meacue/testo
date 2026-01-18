<?php

declare(strict_types=1);

namespace Testo\Assert\Internal;

use Testo\Assert\Exception\StateNotFound;
use Testo\Assert\Internal\Expectation\ExpectExceptionHandler;
use Testo\Assert\StaticState;
use Testo\Attribute\ExpectException;
use Testo\Interceptor\TestRunInterceptor;
use Testo\Module\Interceptor\InterceptorOptions;
use Testo\Test\Dto\TestInfo;
use Testo\Test\Dto\TestResult;

/**
 * Configures expected exceptions for a test based on the {@see ExpectException} attribute.
 */
#[InterceptorOptions(order: InterceptorOptions::ORDER_ASSERTIONS + 10)]
final class ExpectExceptionConfigurator implements TestRunInterceptor
{
    public function __construct(
        private readonly ExpectException $options,
    ) {}

    #[\Override]
    public function runTest(TestInfo $info, callable $next): TestResult
    {
        $context = StaticState::current() ?? throw new StateNotFound();

        $context->expectations[] = new ExpectExceptionHandler(
            classOrObject: $this->options->class,
        );

        return $next($info);
    }
}
