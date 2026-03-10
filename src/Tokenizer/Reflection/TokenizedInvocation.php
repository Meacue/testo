<?php

declare(strict_types=1);

namespace Testo\Tokenizer\Reflection;

use Internal\Path;
use Testo\Tokenizer\Exception\ReflectionException;

/**
 * ReflectionInvocation used to represent function or static method call found by ReflectionFile.
 * This reflection is very useful for static analysis and mainly used in Translator component to
 * index translation function usages.
 *
 * @psalm-immutable
 */
final readonly class TokenizedInvocation
{
    /**
     * New call reflection.
     *
     * @param class-string $class
     * @param TokenizedArgument[] $arguments
     * @param int $level Was a function used inside another function call?
     */
    public function __construct(
        /**
         * Function usage filename.
         */
        public Path $filename,
        /**
         * Function usage line.
         */
        public int $line,
        /**
         * Parent class.
         * @var class-string|""
         */
        public string $class,
        /**
         * Method operator (:: or ->).
         * @var "::"|"->"|""
         */
        public string $operator,
        /**
         * Function or method name.
         * @var non-empty-string
         */
        public string $name,
        /**
         * All parsed function arguments.
         *
         * @var TokenizedArgument[]
         */
        public array $arguments,
        /**
         * Function usage src.
         */
        public string $source,
        /**
         * Invoking level.
         */
        public int $level,
    ) {}

    /**
     * Call made by class method.
     */
    public function isMethod(): bool
    {
        return !empty($this->class);
    }

    /**
     * Get call argument by it position.
     */
    public function getArgument(int $index): TokenizedArgument
    {
        if (!isset($this->arguments[$index])) {
            throw new ReflectionException(\sprintf("No such argument with index '%d'", $index));
        }

        return $this->arguments[$index];
    }
}
