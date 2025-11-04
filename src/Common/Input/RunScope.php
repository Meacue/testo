<?php

declare(strict_types=1);

namespace Testo\Common\Input;

use Testo\Config\Internal\Attribute\InflectableConfig;
use Testo\Config\Internal\Attribute\InputOption;

/**
 * File system scope configuration.
 */
#[InflectableConfig]
final class RunScope
{
    /**
     * @var non-empty-string[]
     */
    #[InputOption('filter')]
    public array $filter = [];

    /**
     * @var non-empty-string[]
     */
    #[InputOption('path')]
    public array $path = [];

    /**
     * @var non-empty-string[]
     */
    #[InputOption('suite')]
    public array $suite = [];
}
