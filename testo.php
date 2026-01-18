<?php

declare(strict_types=1);

use Testo\Framework\Application\Config\ApplicationConfig;
use Testo\Framework\Application\Config\FinderConfig;
use Testo\Framework\Application\Config\SuiteConfig;

return new ApplicationConfig(
    suites: \array_merge(
        [
            new SuiteConfig(
                name: 'default',
                location: new FinderConfig(
                    include: ['tests/Testo'],
                ),
            ),
        ],
        require 'tests/Assert/suites.php',
        require 'tests/Sample/suites.php',
    ),
);
