<?php

declare(strict_types=1);

namespace Testo\Render;

/**
 * Helper utilities for text-based rendering (CLI, TeamCity, logs, etc.).
 *
 * @internal
 */
final class Helper
{
    /**
     * Framework namespaces to exclude in compact mode.
     *
     * @var list<non-empty-string>
     */
    private const FRAMEWORK_NAMESPACES = [
        'Testo\\Test\\Runner\\',
        'Testo\\Module\\Interceptor\\',
        'Testo\\Interceptor\\Reflection\\',
        'Testo\\Interceptor\\TestCaseCallInterceptor\\',
        'Symfony\\Component\\Console\\',
        'Yiisoft\\Injector\\',
    ];

    /**
     * Formats a throwable into a detailed string with class, message, file, line, and stack trace.
     *
     * @param \Throwable $throwable The exception to format
     * @param \ReflectionMethod|\ReflectionFunction|null $testMethod Test method to cut stack trace at
     * @param int<0, max> $maxPreviousDepth Maximum depth for previous exceptions (0 = only main exception)
     * @param bool $compact Use compact format (hide framework internals)
     */
    public static function formatThrowable(
        \Throwable $throwable,
        \ReflectionMethod|\ReflectionFunction|null $testMethod = null,
        int $maxPreviousDepth = 0,
        bool $compact = true,
    ): string {
        $output = [];
        $depth = 0;
        $current = $throwable;

        while ($current !== null && $depth <= $maxPreviousDepth) {
            // Exception header
            $exceptionClass = $current::class;
            if ($depth > 0) {
                $output[] = "\nCaused by: {$exceptionClass}: {$current->getMessage()}";
            } else {
                $output[] = "{$exceptionClass}: {$current->getMessage()}";
            }

            $output[] = "File: {$current->getFile()}:{$current->getLine()}";

            // Format stack trace
            $trace = self::filterStackTrace(
                $current->getTrace(),
                $testMethod,
                $compact,
            );

            $output[] = "\nStack trace:\n{$trace}";

            $current = $current->getPrevious();
            $depth++;
        }

        return \implode("\n", $output);
    }

    /**
     * Filters and formats a stack trace array.
     *
     * @param list<array<string, mixed>> $trace Raw stack trace from Throwable::getTrace()
     * @param \ReflectionMethod|\ReflectionFunction|null $testMethod Test method to cut trace at
     * @param bool $compact Hide framework internals
     * @return non-empty-string Formatted stack trace
     */
    public static function filterStackTrace(
        array $trace,
        \ReflectionMethod|\ReflectionFunction|null $testMethod = null,
        bool $compact = true,
    ): string {
        // Cut trace at test method if provided
        if ($testMethod !== null) {
            $trace = self::cutTraceAtTestMethod($trace, $testMethod);
        }

        // Filter framework frames if compact mode
        if ($compact) {
            [$visibleFrames, $hiddenCount] = self::filterFrameworkFrames($trace);
        } else {
            $visibleFrames = $trace;
            $hiddenCount = 0;
        }

        // Format frames
        $lines = [];
        foreach ($visibleFrames as $i => $frame) {
            $lines[] = self::formatFrame($i, $frame);
        }

        if ($hiddenCount > 0) {
            $lines[] = "\n... {$hiddenCount} internal framework calls hidden ...";
        }

        return $lines !== [] ? \implode("\n", $lines) : 'No stack trace available';
    }

    /**
     * Cuts trace at test method, keeping frames up to and including the test method call.
     *
     * @param list<array<string, mixed>> $trace
     * @param \ReflectionMethod|\ReflectionFunction $testMethod
     * @return list<array<string, mixed>>
     */
    private static function cutTraceAtTestMethod(
        array $trace,
        \ReflectionMethod|\ReflectionFunction $testMethod,
    ): array {
        $testClass = $testMethod instanceof \ReflectionMethod
            ? $testMethod->getDeclaringClass()->getName()
            : null;
        $testFunction = $testMethod->getName();

        foreach ($trace as $i => $frame) {
            $isMatch = match (true) {
                $testClass !== null => ($frame['class'] ?? null) === $testClass
                    && ($frame['function'] ?? null) === $testFunction,
                default => ($frame['function'] ?? null) === $testFunction,
            };

            if ($isMatch) {
                // Return frames up to and including this one
                return \array_slice($trace, 0, $i + 1);
            }
        }

        // Test method not found, return full trace
        return $trace;
    }

    /**
     * Filters out framework internal frames.
     *
     * @param list<array<string, mixed>> $trace
     * @return array{list<array<string, mixed>>, int<0, max>} [visible frames, hidden count]
     */
    private static function filterFrameworkFrames(array $trace): array
    {
        $visible = [];
        $hiddenCount = 0;

        foreach ($trace as $frame) {
            $class = $frame['class'] ?? '';
            $isFramework = false;

            foreach (self::FRAMEWORK_NAMESPACES as $namespace) {
                if (\str_starts_with($class, $namespace)) {
                    $isFramework = true;
                    break;
                }
            }

            if ($isFramework) {
                $hiddenCount++;
            } else {
                $visible[] = $frame;
            }
        }

        return [$visible, $hiddenCount];
    }

    /**
     * Formats a single stack trace frame.
     *
     * @param int<0, max> $index
     * @param array<string, mixed> $frame
     */
    private static function formatFrame(int $index, array $frame): string
    {
        $file = $frame['file'] ?? '[internal function]';
        $line = $frame['line'] ?? 0;
        $class = $frame['class'] ?? '';
        $type = $frame['type'] ?? '';
        $function = $frame['function'] ?? '';

        $location = $line > 0 ? "{$file}:{$line}" : $file;
        $call = $class !== '' ? "{$class}{$type}{$function}()" : "{$function}()";

        return "#{$index} {$location}\n   {$call}";
    }
}
