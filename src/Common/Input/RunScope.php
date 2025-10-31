<?php

declare(strict_types=1);

namespace Testo\Common\Input;

use Testo\Config\Internal\Attribute\InflectableConfig;
use Testo\Config\Internal\Attribute\InputArgument;
use Testo\Config\Internal\Attribute\InputOption;

/**
 * File system scope configuration.
 */
#[InflectableConfig]
final class RunScope
{
    /**
     * Comma-separated list of filters to apply.
     *
     * @var non-empty-string[]
     */
    #[InputOption('filter')]
    public array $filter = [];

    #[InputArgument('path')]
    public string $path = '';
}
