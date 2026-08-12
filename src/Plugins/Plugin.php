<?php

declare(strict_types=1);

namespace Libxa\Admin\Plugins;

use Libxa\Admin\Panel\AdminPanel;

/**
 * The contract a LibAdmin plugin implements.
 *
 * A plugin is a Composer package that adds to the panel: resources, pages,
 * widgets, navigation, or markup at a hook. It never edits the panel's own
 * code, which is what makes it possible to install two of them and have both
 * keep working after an upgrade.
 *
 * The lifecycle is deliberately two calls rather than one:
 *
 *   register()  Declare what you provide. Nothing else is guaranteed to have
 *               registered yet, so do not look for anything here.
 *   boot()      Everything is registered. Safe to inspect the panel, react to
 *               another plugin, or decide based on what exists.
 *
 * A plugin that reads the panel during register() sees whatever happened to be
 * loaded first, which makes behaviour depend on Composer's autoload order.
 * That is a bug that appears when an unrelated package is installed.
 */
interface Plugin
{
    /**
     * A stable identifier, e.g. "acme/blog".
     *
     * Used for ordering, for dependency declarations, and in the error when
     * two plugins collide. Vendor-prefixed so two authors can both ship a
     * plugin called "blog".
     */
    public function id(): string;

    /** Human-readable, for the plugin list in the panel. */
    public function name(): string;

    public function version(): string;

    /**
     * Declare what this plugin provides.
     *
     * Called for every plugin before any boot(). Register resources, pages,
     * widgets and navigation here.
     */
    public function register(AdminPanel $panel): void;

    /**
     * Everything is registered.
     *
     * Called for every plugin after all register() calls. Inspect the panel,
     * attach hooks, or react to what another plugin provided.
     */
    public function boot(AdminPanel $panel): void;

    /**
     * Plugin ids this one needs, if any.
     *
     * Declared rather than assumed: the manager orders boot() so a dependency
     * boots first, and refuses to boot a plugin whose dependency is missing
     * rather than letting it fail somewhere less obvious.
     *
     * @return list<string>
     */
    public function requires(): array;
}
