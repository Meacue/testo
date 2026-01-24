<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

use Testo\Common\Reflection;

#[CallStackAttribute('topFunction')]
function topLevelFunction(
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

#[CallStackAttribute('nestedFunction')]
function nestedFunction(
    ?string $attributeClass = null,
    bool $includePrototypes = true,
    bool $includeClasses = false,
    bool $includeParents = true,
    bool $includeTraits = true,
    int $limit = \PHP_INT_MAX,
): array {
    return topLevelFunction(
        $attributeClass,
        $includePrototypes,
        $includeClasses,
        $includeParents,
        $includeTraits,
        $limit,
    );
}

function unmarkedFunction(
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
