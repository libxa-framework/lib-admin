<?php

declare(strict_types=1);

namespace Libxa\Admin\Plugins;

use Libxa\Admin\Panel\AdminPanel;
use RuntimeException;
use Throwable;

/**
 * Loads plugins, in an order their dependencies allow.
 *
 * Two properties matter more than features here.
 *
 * **A broken plugin must not take the panel down.** An admin panel is what you
 * log into when something is wrong, so a plugin that throws while booting has
 * to be recorded and skipped rather than allowed to produce a white screen on
 * the one page you needed. `failures()` reports what was skipped and why.
 *
 * **Order must not depend on Composer.** Plugins declare what they require and
 * are sorted accordingly, so installing an unrelated package cannot change the
 * order the panel assembles in.
 */
final class PluginManager
{
    /** @var array<string, Plugin> */
    private array $plugins = [];

    /** @var array<string, string> plugin id => why it was skipped */
    private array $failures = [];

    private bool $booted = false;

    /**
     * Add a plugin.
     *
     * Two plugins claiming the same id is refused outright: it means one of
     * them silently replaces the other, and which one wins would depend on
     * load order.
     */
    public function add(Plugin $plugin): void
    {
        $id = $plugin->id();

        if (isset($this->plugins[$id])) {
            throw new RuntimeException(
                "Two plugins are registered as [{$id}]: "
                . $this->plugins[$id]::class . ' and ' . $plugin::class . '.',
            );
        }

        if ($this->booted) {
            throw new RuntimeException(
                "[{$id}] was added after the panel booted. Register plugins from a "
                . 'service provider so they are all present before boot.',
            );
        }

        $this->plugins[$id] = $plugin;
    }

    /** @return array<string, Plugin> */
    public function all(): array
    {
        return $this->plugins;
    }

    public function has(string $id): bool
    {
        return isset($this->plugins[$id]);
    }

    public function get(string $id): ?Plugin
    {
        return $this->plugins[$id] ?? null;
    }

    /** Plugins that were skipped, and why. Shown in the panel. */
    public function failures(): array
    {
        return $this->failures;
    }

    /**
     * Register then boot every plugin.
     *
     * Two passes on purpose: a plugin that inspects the panel during
     * registration sees only what happened to load first, which makes its
     * behaviour depend on an unrelated package being installed.
     */
    public function load(AdminPanel $panel): void
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        $ordered = $this->resolveOrder();

        foreach ($ordered as $plugin) {
            $this->guard($plugin, static fn () => $plugin->register($panel));
        }

        foreach ($ordered as $plugin) {
            if (isset($this->failures[$plugin->id()])) {
                // It failed to register, so its own state is unknown. Booting
                // it anyway is how a half-registered plugin corrupts the panel.
                continue;
            }

            // A dependency can only fail during the register pass, which is
            // after the order was worked out, so this cannot be decided
            // earlier. Booting a plugin whose dependency did not register is
            // booting it against something half-built.
            $broken = $this->failedDependency($plugin);

            if ($broken !== null) {
                $this->failures[$plugin->id()] =
                    "Requires [{$broken}], which failed to register.";

                continue;
            }

            $this->guard($plugin, static fn () => $plugin->boot($panel));
        }
    }

    /**
     * Plugins in dependency order.
     *
     * A missing dependency is recorded and the plugin skipped, rather than
     * left to fail later with an error that names the wrong thing. A cycle is
     * recorded for every plugin in it, since there is no correct order and
     * picking one arbitrarily hides the mistake.
     *
     * @return list<Plugin>
     */
    private function resolveOrder(): array
    {
        $ordered = [];
        $state = [];

        $visit = function (string $id, array $trail) use (&$visit, &$ordered, &$state): void {
            if (($state[$id] ?? null) === 'done') {
                return;
            }

            if (($state[$id] ?? null) === 'visiting') {
                $cycle = implode(' -> ', [...$trail, $id]);

                foreach ($trail as $member) {
                    $this->failures[$member] = "Circular dependency: {$cycle}.";
                }

                return;
            }

            $plugin = $this->plugins[$id] ?? null;

            if ($plugin === null) {
                return;
            }

            $state[$id] = 'visiting';

            foreach ($plugin->requires() as $dependency) {
                if (! isset($this->plugins[$dependency])) {
                    $this->failures[$id] = "Requires [{$dependency}], which is not installed.";
                    $state[$id] = 'done';

                    return;
                }

                $visit($dependency, [...$trail, $id]);

                if (isset($this->failures[$dependency])) {
                    // Only when this plugin has no reason recorded already.
                    // A member of a cycle is marked by the branch above, and
                    // overwriting that with a generic message loses the one
                    // piece of information that explains the whole situation.
                    $this->failures[$id] ??= "Requires [{$dependency}], which could not be loaded.";
                    $state[$id] = 'done';

                    return;
                }
            }

            $state[$id] = 'done';
            $ordered[] = $plugin;
        };

        foreach (array_keys($this->plugins) as $id) {
            $visit($id, []);
        }

        return array_values(array_filter(
            $ordered,
            fn (Plugin $p): bool => ! isset($this->failures[$p->id()]),
        ));
    }

    /**
     * The first dependency of this plugin that failed, if any.
     *
     * Walked rather than checked one level deep, so a plugin three removed
     * from the broken one is skipped too. Depth is bounded by the number of
     * plugins, and a cycle is already refused before this runs.
     */
    private function failedDependency(Plugin $plugin, array $seen = []): ?string
    {
        foreach ($plugin->requires() as $id) {
            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;

            if (isset($this->failures[$id])) {
                return $id;
            }

            $dependency = $this->plugins[$id] ?? null;

            if ($dependency !== null) {
                $deeper = $this->failedDependency($dependency, $seen);

                if ($deeper !== null) {
                    return $deeper;
                }
            }
        }

        return null;
    }

    /**
     * Run one lifecycle call, recording a failure instead of propagating it.
     *
     * The panel is what you open when something is already wrong. A plugin
     * throwing during boot must not be the reason you cannot look.
     */
    private function guard(Plugin $plugin, callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            $this->failures[$plugin->id()] = $e->getMessage();
        }
    }
}
