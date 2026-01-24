<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

use Testo\Common\Reflection;

#[CallStackAttribute('baseClassAttribute')]
abstract class CallStackBaseClass
{
    #[CallStackAttribute('baseMethod')]
    public function baseMethod(
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

    #[CallStackAttribute('overridden')]
    public function overriddenMethod(
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
}
