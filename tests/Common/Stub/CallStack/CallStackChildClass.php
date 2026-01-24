<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

use Testo\Common\Reflection;

final class CallStackChildClass extends CallStackBaseClass
{
    public function overriddenMethod(?string $attributeClass = null, bool $includePrototypes = true): array
    {
        return Reflection::getAttributesFromCallStack($attributeClass, $includePrototypes);
    }

    #[CallStackAttribute('childMethod')]
    public function childMethod(?string $attributeClass = null, bool $includePrototypes = true): array
    {
        return $this->overriddenMethod($attributeClass, $includePrototypes);
    }
}
