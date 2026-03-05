<?php

declare(strict_types=1);

namespace Testo\Core\Value;

enum TestType: string
{
    /**
     * A separated test method.
     */
    case Test = 'test';

    /**
     * A test declared in metadata of the tested unit, e.g. a method with #[TestInline] attribute.
     */
    case TestInline = 'test-inline';

    /**
     * A benchmark declared in metadata of the compared unit, e.g. a method with #[BenchWith] attribute.
     */
    case BenchInline = 'bench';

    /**
     * A profile declared in metadata of the profiled unit.
     */
    case ProfileInline = 'profile';
}
