<?php

declare(strict_types=1);

namespace Testo\Application\Config\Plugin;

use Testo\Application\Config\PluginConfigurator;

$_ = [];
// \class_exists(TeamcityPlugin::class) and $_[] = new TeamcityPlugin();
// \class_exists(TerminalPlugin::class) and $_[] = new TerminalPlugin();

\define([__NAMESPACE__ . '\DEFAULT_APPLICATION_PLUGINS'][0], $_);
unset($_);

/**
 * Application-level plugin configuration facade.
 *
 * ```
 * // Add to defaults
 * ApplicationPlugins::with(new MyPlugin())
 *
 * // Replace defaults entirely
 * ApplicationPlugins::only(new MyPlugin())
 *
 * // Chaining
 * ApplicationPlugins::with(new A())->without(B::class)->with(new C())
 * ```
 *
 * @api
 */
final class ApplicationPlugins
{
    /**
     * Default plugins + the given plugins.
     */
    public static function with(PluginConfigurator ...$plugins): PluginCollection
    {
        return self::defaults()->with(...$plugins);
    }

    /**
     * Default plugins minus the specified classes.
     *
     * @param class-string<PluginConfigurator> ...$pluginClasses
     */
    public static function without(string ...$pluginClasses): PluginCollection
    {
        return self::defaults()->without(...$pluginClasses);
    }

    /**
     * No defaults — only the specified plugins.
     */
    public static function only(PluginConfigurator ...$plugins): PluginCollection
    {
        return new PluginCollection(...$plugins);
    }

    /**
     * Returns the default application-level plugins.
     */
    public static function defaults(): PluginCollection
    {
        return new PluginCollection(...DEFAULT_APPLICATION_PLUGINS);
    }
}
