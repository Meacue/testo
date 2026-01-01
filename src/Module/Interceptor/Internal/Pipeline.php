<?php

declare(strict_types=1);

namespace Testo\Module\Interceptor\Internal;

use Testo\Module\Interceptor\Internal\InterceptorMarker as TInterceptor;

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
    ) {
        // Reset keys
        $this->interceptors = Sorter::sortAndFilter($interceptors);
    }

    /**
     * Make sure that interceptors implement the same interface.
     * @template-covariant TInt of TInterceptor
     * @template TIn
     * @template-covariant TOut
     * @param TInterceptor ...$interceptors Instantiated interceptors.
     * @return self<TInt, TIn, TOut>
     */
    public static function prepare(TInterceptor ...$interceptors): self
    {
        return new self($interceptors);
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
        $new->interceptors = Sorter::sortAndFilter($interceptors);
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
