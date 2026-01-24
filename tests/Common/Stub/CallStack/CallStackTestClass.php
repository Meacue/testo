<?php

declare(strict_types=1);

namespace Tests\Common\Stub\CallStack;

use Testo\Common\Reflection;

final class CallStackTestClass
{
    #[CallStackAttribute('methodA')]
    public function methodA(?string $attributeClass = null, bool $includePrototypes = true): array
    {
        return Reflection::getAttributesFromCallStack($attributeClass, $includePrototypes);
    }

    #[CallStackAttribute('methodB')]
    public function methodB(?string $attributeClass = null, bool $includePrototypes = true): array
    {
        return $this->methodA($attributeClass, $includePrototypes);
    }

    public function unmarkedMethod(?string $attributeClass = null, bool $includePrototypes = true): array
    {
        return $this->methodA($attributeClass, $includePrototypes);
    }
}
