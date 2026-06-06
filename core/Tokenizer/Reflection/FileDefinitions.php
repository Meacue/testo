<?php

declare(strict_types=1);

namespace Testo\Tokenizer\Reflection;

use Testo\Core\Definition\CaseDefinitions;

/**
 * Immutable container of the PHP definitions found in a single tokenized file.
 *
 * A pure DTO: the reflections are extracted by {@see \Testo\Tokenizer\DefinitionLocator} and handed in
 * ready-made (see {@see \Testo\Application\Internal\SuiteFactory::create()}).
 */
final readonly class FileDefinitions
{
    /**
     * @param array<class-string, \ReflectionClass> $classes Class reflections found in the file.
     * @param array<class-string, \ReflectionClass> $interfaces Interface reflections found in the file.
     * @param array<class-string, \ReflectionEnum> $enums Enum reflections found in the file.
     * @param array<string, \ReflectionFunction> $functions Function reflections found in the file.
     * @param array<class-string, \ReflectionClass> $traits Trait reflections found in the file.
     */
    public function __construct(
        public TokenizedFile $tokenizedFile,
        public CaseDefinitions $cases = new CaseDefinitions(),
        public array $classes = [],
        public array $interfaces = [],
        public array $enums = [],
        public array $functions = [],
        public array $traits = [],
    ) {}
}
