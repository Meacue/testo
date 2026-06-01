<?php

declare(strict_types=1);

namespace Testo\Testing\Attribute;

use Internal\Path;
use Testo\Common\PluginConfigurator;

/**
 * Configure Test Suite for the testing tools.
 *
 * @internal
 * @psalm-internal Testo
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final readonly class TestingSuite
{
    /** @var list<class-string<PluginConfigurator>|PluginConfigurator> */
    public array $plugins;

    /**
     * @param string|Path $path Stub directory or file path.
     * @param list<class-string<PluginConfigurator>|PluginConfigurator> $plugins Extra plugins to load
     *        for the testing suite, on top of the suite defaults. Useful to exercise a plugin's
     *        runtime behaviour end-to-end (e.g. output capturing).
     */
    public function __construct(
        public string|Path $path,
        array $plugins = [],
    ) {
        $this->plugins = $plugins;
    }
}
