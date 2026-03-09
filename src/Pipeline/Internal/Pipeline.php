<?php

declare(strict_types=1);

namespace Testo\Pipeline\Internal;

use Testo\Pipeline\Interceptor as TInterceptor;

/**
 * Processor for interceptors chain.
 *
 * @template-covariant TClass of TInterceptor
 * @template TInput
 * @template-covariant TOutput of mixed
 *
 * @psalm-immutable
 *
 * @internal
 * @psalm-internal Testo
 */
final class Pipeline
{
    /** @var non-empty-string */
    private string $method;

    /** @var callable(TInput): TOutput */
    private mixed $last;

    /** @var list<TInterceptor> */
    private array $interceptors = [];

    /** @var int<0, max> Current interceptor key */
    private int $current = 0;

    /**
     * @param TInterceptor[] $interceptors
     */
    private function __construct(
        array $interceptors,
        private readonly ?string $testType,
    ) {
        // Reset keys
        $this->interceptors = Sorter::sortAndFilter($interceptors, $testType);
    }

    /**
     * Create a pipeline with given interceptors.
     *
     * @template-covariant TInt of TInterceptor
     * @template TIn
     * @template-covariant TOut
     * @param \BackedEnum|non-empty-string|null $testType Type of tests to filter interceptors by.
     *        If null, all interceptors are included. {@see CaseDefinition::$type}
     * @param TInterceptor ...$interceptors Instantiated interceptors.
     * @return self<TInt, TIn, TOut>
     *
     * @note Make sure that interceptors implement the same interface.
     */
    public static function prepare(\BackedEnum|string|null $testType, TInterceptor ...$interceptors): self
    {
        return new self($interceptors, \is_object($testType) ? $testType->value : $testType);
    }

    /**
     * Add interceptors to the pipeline.
     *
     * All the remaining interceptors will be sorted and combined into a new single interceptor.
     *
     * @param TInterceptor ...$interceptors Instantiated interceptors.
     * @return self<TInterceptor, TInput, TOutput>
     */
    public function combine(TInterceptor ...$interceptors): self
    {
        # Merge remaining interceptors with new ones
        $interceptors = \array_merge(\array_slice($this->interceptors, $this->current), $interceptors);

        # Create a new pipeline with merged interceptors
        $new = clone $this;
        $new->current = 0;
        $new->interceptors = Sorter::sortAndFilter($interceptors, $this->testType);
        return $new;
    }

    /**
     * @param non-empty-string $method Method name of the all interceptors.
     *
     * @return callable(object): TOutput
     */
    public function with(callable $last, string $method): callable
    {
        $new = clone $this;

        $new->last = $last;
        $new->method = $method;

        return $new;
    }

    /**
     * Must be used after {@see self::with()} method.
     *
     * @param TInput $input Input value for the first interceptor.
     *
     * @return TOutput
     */
    public function __invoke(object $input): mixed
    {
        $interceptor = $this->interceptors[$this->current] ?? null;

        if ($interceptor === null) {
            return ($this->last)($input);
        }

        $next = $this->next();

        return $interceptor->{$this->method}($input, $next);
    }

    private function next(): self
    {
        $new = clone $this;
        ++$new->current;

        return $new;
    }
}
