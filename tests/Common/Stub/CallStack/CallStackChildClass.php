<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

use Testo\Common\Reflection;

#[CallStackAttribute('childClassAttribute')]
final class CallStackChildClass extends CallStackBaseClass
{
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

    #[CallStackAttribute('childMethod')]
    public function childMethod(
        ?string $attributeClass = null,
        bool $includePrototypes = true,
        bool $includeClasses = false,
        bool $includeParents = true,
        bool $includeTraits = true,
        int $limit = \PHP_INT_MAX,
    ): array {
        return $this->overriddenMethod(
            $attributeClass,
            $includePrototypes,
            $includeClasses,
            $includeParents,
            $includeTraits,
            $limit,
        );
    }
}
