<?php

declare(strict_types=1);

namespace Testo\Facade\Exception;

use Testo\Facade\FacadePlugin;

final class ContainerNotFound extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct(\sprintf(
            <<<TEXT
                No active container found.
                The static `Testo` facade is only available with the `%s` plugin installed.
                TEXT,
            FacadePlugin::class,
        ));
    }
}
