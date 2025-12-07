<?php

declare(strict_types=1);

namespace Testo\Assert\State;

/**
 * Collector of assertion records.
 */
interface CompositeRecord extends Record
{
    /**
     * Get all collected records.
     *
     * @return Record[]
     */
    public function getRecords(): array;
}
