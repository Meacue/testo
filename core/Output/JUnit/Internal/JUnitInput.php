<?php

declare(strict_types=1);

namespace Testo\Output\JUnit\Internal;

use Testo\Application\Config\Internal\Attribute\InflectableConfig;
use Testo\Application\Config\Internal\Attribute\InputOption;

/**
 * CLI input for the JUnit reporter.
 *
 * The `--log-junit=<path>` flag overrides the `outputPath` constructor
 * argument of {@see \Testo\Output\JUnit\JUnitPlugin}. The flag name mirrors
 * PHPUnit / Pest / ParaTest so external tools (e.g. Infection's bridge) can
 * pass the path through using the convention they already speak.
 *
 * @internal
 * @psalm-internal Testo\Output\JUnit
 */
#[InflectableConfig]
final class JUnitInput
{
    #[InputOption('log-junit')]
    public ?string $outputPath = null;
}
