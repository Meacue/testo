<?php

declare(strict_types=1);

namespace Testo\Assert\State\Expectation;

final class Fail extends ExpectationFailed
{
    public function __construct(string $context)
    {
        parent::__construct('Test failed', $context, '', '');
    }
}
