<?php

declare(strict_types=1);

namespace Tests\Output\Unit\Rendering;

use Testo\Assert;
use Testo\Output\Rendering\StackTrace;
use Testo\Test;
use Tests\Output\Stub\AssertMethodStub;
use Tests\Output\Stub\MiddlewareStub;
use Tests\Output\Stub\ThrowingStub;

#[Test]
final class StackTraceTest
{
    public function emptyTraceReturnsEmpty(): void
    {
        // Act & Assert
        Assert::same(StackTrace::cutStackTrace([]), []);
    }

    /**
     * `Throwable::getTrace()` and `debug_backtrace()` produce slightly different frame
     * layouts (the former omits the throwing site itself). Both shapes must trim the
     * same way, so the behavior is exercised against each — this one covers exceptions.
     */
    public function cutsFramesBelowAssertMethodFromExceptionTrace(): void
    {
        // Arrange
        try {
            AssertMethodStub::run(ThrowingStub::fail(...));
        } catch (\RuntimeException $e) {
            $trace = $e->getTrace();
        }

        // Act
        $result = StackTrace::cutStackTrace($trace);

        // Assert: AssertMethodStub::run is the first frame, ThrowingStub::fail is removed
        Assert::true(\count($result) < \count($trace));
        Assert::same($result[0]['class'], AssertMethodStub::class);
        Assert::same($result[0]['function'], 'run');
        $hasThrowingFrames = \array_filter(
            $result,
            static fn(array $f): bool => ($f['class'] ?? null) === ThrowingStub::class,
        );
        Assert::same($hasThrowingFrames, []);
    }

    /**
     * Companion to {@see cutsFramesBelowAssertMethodFromExceptionTrace} — same trim
     * behavior is required when the trace was captured via `debug_backtrace()` rather
     * than thrown.
     */
    public function cutsFramesBelowAssertMethodFromDebugBacktrace(): void
    {
        // Arrange
        $trace = AssertMethodStub::run(ThrowingStub::captureTrace(...));

        // Act
        $result = StackTrace::cutStackTrace($trace);

        // Assert: AssertMethodStub::run is the first frame, ThrowingStub::captureTrace is removed
        Assert::true(\count($result) < \count($trace));
        Assert::same($result[0]['class'], AssertMethodStub::class);
        Assert::same($result[0]['function'], 'run');
        $hasThrowingFrames = \array_filter(
            $result,
            static fn(array $f): bool => ($f['class'] ?? null) === ThrowingStub::class,
        );
        Assert::same($hasThrowingFrames, []);
    }

    public function doesNotAssertMethodWithoutAttribute(): void
    {
        // Arrange
        try {
            MiddlewareStub::run(ThrowingStub::fail(...));
        } catch (\RuntimeException $e) {
            $trace = $e->getTrace();
        }

        // Act
        $result = StackTrace::cutStackTrace($trace);

        // Assert
        Assert::same($result, $trace);
    }

    public function cutsAtOutermostAssertMethodWithMultipleAttributes(): void
    {
        // Arrange: outer AssertMethod -> closure -> inner AssertMethod -> fail
        try {
            AssertMethodStub::run(static fn() => AssertMethodStub::run(ThrowingStub::fail(...)));
        } catch (\RuntimeException $e) {
            $trace = $e->getTrace();
        }

        // Act
        $result = StackTrace::cutStackTrace($trace);

        // Assert: outer AssertMethod is the first frame, inner AssertMethod and deeper are removed
        Assert::true(\count($result) < \count($trace));
        Assert::same($result[0]['class'], AssertMethodStub::class);
        Assert::same($result[0]['function'], 'run');
        $cutFrames = \array_filter(
            $result,
            static fn(array $f): bool => ($f['class'] ?? null) === AssertMethodStub::class,
        );
        Assert::count($cutFrames, 1);
    }

    /**
     * Without a boundary, the search is capped at {@see StackTrace::SEARCH_DEPTH} frames
     * to keep the scan cheap. `MiddlewareStub::runDeep` injects enough internal frames
     * to push the AssertMethod past that cap, so it must not be found.
     */
    public function doesNotCutBeyondDepthLimit(): void
    {
        // Arrange: AssertMethod is beyond SEARCH_DEPTH from the start
        try {
            AssertMethodStub::run(static fn() => MiddlewareStub::runDeep(ThrowingStub::fail(...)));
        } catch (\RuntimeException $e) {
            $trace = $e->getTrace();
        }

        // Act
        $result = StackTrace::cutStackTrace($trace);

        // Assert: AssertMethod too far from the error, nothing is cut
        Assert::same($result, $trace);
    }

    /**
     * The boundary represents the test function frame: anything below it is application
     * code, anything above is runner internals. AssertMethod is only meaningful below
     * the boundary, so reaching the boundary must abort the search.
     */
    public function boundaryStopsAssertMethodSearch(): void
    {
        // Arrange: AssertMethod -> middleware -> boundary (no AssertMethod between error and boundary)
        try {
            AssertMethodStub::run(static fn() => MiddlewareStub::run(ThrowingStub::fail(...)));
        } catch (\RuntimeException $e) {
            $trace = $e->getTrace();
        }
        $boundary = new \ReflectionMethod(MiddlewareStub::class, 'run');

        // Act: boundary stops search before AssertMethod is reached
        $result = StackTrace::cutStackTrace($trace, $boundary, false);

        // Assert: trace unchanged — AssertMethod is after boundary, not found
        Assert::same($result, $trace);
    }

    /**
     * Inverse of {@see boundaryStopsAssertMethodSearch}: when AssertMethod sits between
     * the error and the boundary, it is found first and frames below it are trimmed
     * normally.
     */
    public function boundaryWithAssertMethodBeforeBoundary(): void
    {
        // Arrange: error -> AssertMethod -> middleware -> boundary
        try {
            MiddlewareStub::run(
                static fn() => AssertMethodStub::run(ThrowingStub::fail(...)),
            );
        } catch (\RuntimeException $e) {
            $trace = $e->getTrace();
        }
        $boundary = new \ReflectionMethod(MiddlewareStub::class, 'run');

        // Act
        $result = StackTrace::cutStackTrace($trace, $boundary);

        // Assert: AssertMethod found before boundary, internal frames cut
        Assert::same($result[0]['class'], AssertMethodStub::class);
        Assert::same($result[0]['function'], 'run');
    }

    /**
     * The {@see StackTrace::SEARCH_DEPTH} cap exists only to bound an open-ended scan.
     * Once a boundary is supplied, the search has a natural stop point, so the cap is
     * lifted and an AssertMethod arbitrarily deep before the boundary still trims.
     */
    public function boundaryBypassesDepthLimit(): void
    {
        // Arrange: AssertMethod is beyond SEARCH_DEPTH but before boundary
        try {
            MiddlewareStub::run(static fn() => AssertMethodStub::run(
                static fn() => MiddlewareStub::runDeep(ThrowingStub::fail(...)),
            ));
        } catch (\RuntimeException $e) {
            $trace = $e->getTrace();
        }
        $boundary = new \ReflectionMethod(MiddlewareStub::class, 'run');

        // Act
        $result = StackTrace::cutStackTrace($trace, $boundary);

        // Assert: AssertMethod found despite being beyond SEARCH_DEPTH
        Assert::same($result[0]['class'], AssertMethodStub::class);
        Assert::same($result[0]['function'], 'run');
    }

    /**
     * `trimAtBoundary: true` makes the boundary act as the trace tail — frames *above*
     * the boundary (runner internals invoking the test) are dropped, leaving the
     * boundary itself as the last frame of the result.
     */
    public function trimAtBoundaryCutsFramesAfterBoundary(): void
    {
        // Arrange
        try {
            MiddlewareStub::run(ThrowingStub::fail(...));
        } catch (\RuntimeException $e) {
            $trace = $e->getTrace();
        }
        $boundary = new \ReflectionMethod(MiddlewareStub::class, 'run');

        // Act
        $result = StackTrace::cutStackTrace($trace, $boundary, trimAtBoundary: true);

        // Assert: trace ends at the boundary method
        Assert::true(\count($result) < \count($trace));
        $lastFrame = $result[\count($result) - 1];
        Assert::same($lastFrame['class'], MiddlewareStub::class);
        Assert::same($lastFrame['function'], 'run');
    }
}
