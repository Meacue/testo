<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

use Testo\Common\Reflection;

#[CallStackAttribute('topFunction')]
function topLevelFunction(?string $attributeClass = null, bool $includePrototypes = true): array
{
    return Reflection::getAttributesFromCallStack($attributeClass, $includePrototypes);
}

#[CallStackAttribute('nestedFunction')]
function nestedFunction(?string $attributeClass = null, bool $includePrototypes = true): array
{
    return topLevelFunction($attributeClass, $includePrototypes);
}

function unmarkedFunction(?string $attributeClass = null, bool $includePrototypes = true): array
{
    return Reflection::getAttributesFromCallStack($attributeClass, $includePrototypes);
}
