<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

use Testo\Common\Reflection;

abstract class CallStackBaseClass
{
    #[CallStackAttribute('baseMethod')]
    public function baseMethod(?string $attributeClass = null, bool $includePrototypes = true): array
    {
        return Reflection::getAttributesFromCallStack($attributeClass, $includePrototypes);
    }

    #[CallStackAttribute('overridden')]
    public function overriddenMethod(?string $attributeClass = null, bool $includePrototypes = true): array
    {
        return Reflection::getAttributesFromCallStack($attributeClass, $includePrototypes);
    }
}
