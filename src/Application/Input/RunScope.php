<?php

declare(strict_types=1);

namespace Testo\Application\Input;

use Testo\Application\Config\Internal\Attribute\InflectableConfig;
use Testo\Application\Config\Internal\Attribute\InputOption;

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

    /**
     * @var non-empty-string|null
     */
    #[InputOption('type')]
    public ?string $type = null;
}
