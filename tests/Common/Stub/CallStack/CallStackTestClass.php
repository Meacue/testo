<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

use Testo\Common\Reflection;

#[CallStackAttribute('classAttribute')]
final class CallStackTestClass
{
    #[CallStackAttribute('methodA')]
    public function methodA(
        ?string $attributeClass = null,
        bool $includePrototypes = true,
        bool $includeClasses = false,
        bool $includeParents = true,
        bool $includeTraits = true,
        int $limit = \PHP_INT_MAX,
    ): array {
        return Reflection::getAttributesFromCallStack(
            $attributeClass,
            $includePrototypes,
            $includeClasses,
            $includeParents,
            $includeTraits,
            $limit,
        );
    }

    #[CallStackAttribute('methodB')]
    public function methodB(
        ?string $attributeClass = null,
        bool $includePrototypes = true,
        bool $includeClasses = false,
        bool $includeParents = true,
        bool $includeTraits = true,
        int $limit = \PHP_INT_MAX,
    ): array {
        return $this->methodA(
            $attributeClass,
            $includePrototypes,
            $includeClasses,
            $includeParents,
            $includeTraits,
            $limit,
        );
    }

    public function unmarkedMethod(
        ?string $attributeClass = null,
        bool $includePrototypes = true,
        bool $includeClasses = false,
        bool $includeParents = true,
        bool $includeTraits = true,
        int $limit = \PHP_INT_MAX,
    ): array {
        return $this->methodA(
            $attributeClass,
            $includePrototypes,
            $includeClasses,
            $includeParents,
            $includeTraits,
            $limit,
        );
    }
}
