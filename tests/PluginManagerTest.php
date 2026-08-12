<?php

declare(strict_types=1);

namespace Libxa\Admin\Tests;

use Libxa\Admin\Panel\AdminPanel;
use Libxa\Admin\Plugins\Plugin;
use Libxa\Admin\Plugins\PluginManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * The plugin lifecycle.
 *
 * The property worth protecting above all others: a broken plugin must not
 * take the panel down. An admin panel is what you open when something is
 * already wrong, and a third-party package throwing during boot must not be
 * the reason you cannot look.
 */
final class PluginManagerTest extends TestCase
{
    private function plugin(
        string $id,
        array $requires = [],
        ?callable $onRegister = null,
        ?callable $onBoot = null,
    ): Plugin {
        return new class ($id, $requires, $onRegister, $onBoot) implements Plugin {
            public function __construct(
                private string $id,
                private array $requires,
                private $onRegister,
                private $onBoot,
            ) {
            }

            public function id(): string
            {
                return $this->id;
            }

            public function name(): string
            {
                return ucfirst($this->id);
            }

            public function version(): string
            {
                return '1.0.0';
            }

            public function requires(): array
            {
                return $this->requires;
            }

            public function register(AdminPanel $panel): void
            {
                if ($this->onRegister !== null) {
                    ($this->onRegister)($panel);
                }
            }

            public function boot(AdminPanel $panel): void
            {
                if ($this->onBoot !== null) {
                    ($this->onBoot)($panel);
                }
            }
        };
    }

    public function test_a_plugin_is_registered_then_booted(): void
    {
        $calls = [];

        $manager = new PluginManager();
        $manager->add($this->plugin(
            'acme/blog',
            onRegister: static function () use (&$calls) { $calls[] = 'register'; },
            onBoot: static function () use (&$calls) { $calls[] = 'boot'; },
        ));

        $manager->load(new AdminPanel());

        self::assertSame(['register', 'boot'], $calls);
    }

    public function test_every_plugin_registers_before_any_boots(): void
    {
        // Two passes, so a plugin inspecting the panel during boot sees what
        // every other plugin provided. With one pass it would see only the
        // ones that happened to load first, making behaviour depend on
        // Composer's autoload order.
        $calls = [];

        $manager = new PluginManager();

        foreach (['a', 'b'] as $id) {
            $manager->add($this->plugin(
                "acme/{$id}",
                onRegister: static function () use (&$calls, $id) { $calls[] = "register:{$id}"; },
                onBoot: static function () use (&$calls, $id) { $calls[] = "boot:{$id}"; },
            ));
        }

        $manager->load(new AdminPanel());

        self::assertSame(
            ['register:a', 'register:b', 'boot:a', 'boot:b'],
            $calls,
        );
    }

    public function test_two_plugins_with_the_same_id_are_refused(): void
    {
        // Otherwise one silently replaces the other, and which one wins
        // depends on load order.
        $manager = new PluginManager();
        $manager->add($this->plugin('acme/blog'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/acme\/blog/');

        $manager->add($this->plugin('acme/blog'));
    }

    // ── failure containment ──────────────────────────────────────────────

    public function test_a_plugin_that_throws_while_booting_does_not_take_the_panel_down(): void
    {
        $manager = new PluginManager();

        $manager->add($this->plugin(
            'acme/broken',
            onBoot: static fn () => throw new RuntimeException('boom'),
        ));

        $healthy = false;
        $manager->add($this->plugin(
            'acme/healthy',
            onBoot: static function () use (&$healthy) { $healthy = true; },
        ));

        $manager->load(new AdminPanel());

        self::assertTrue($healthy, 'a healthy plugin must still boot');
        self::assertArrayHasKey('acme/broken', $manager->failures());
        self::assertStringContainsString('boom', $manager->failures()['acme/broken']);
    }

    public function test_a_plugin_that_fails_to_register_is_not_booted(): void
    {
        // Its own state is unknown after a failed registration, and booting it
        // anyway is how a half-registered plugin corrupts the panel.
        $booted = false;

        $manager = new PluginManager();
        $manager->add($this->plugin(
            'acme/broken',
            onRegister: static fn () => throw new RuntimeException('nope'),
            onBoot: static function () use (&$booted) { $booted = true; },
        ));

        $manager->load(new AdminPanel());

        self::assertFalse($booted);
    }

    // ── dependencies ─────────────────────────────────────────────────────

    public function test_a_dependency_boots_first(): void
    {
        $order = [];

        $manager = new PluginManager();

        // Added in the wrong order deliberately: the result must come from the
        // declared dependency, not from insertion order.
        $manager->add($this->plugin(
            'acme/child',
            requires: ['acme/parent'],
            onBoot: static function () use (&$order) { $order[] = 'child'; },
        ));
        $manager->add($this->plugin(
            'acme/parent',
            onBoot: static function () use (&$order) { $order[] = 'parent'; },
        ));

        $manager->load(new AdminPanel());

        self::assertSame(['parent', 'child'], $order);
    }

    public function test_a_missing_dependency_skips_the_plugin_and_says_which(): void
    {
        $booted = false;

        $manager = new PluginManager();
        $manager->add($this->plugin(
            'acme/child',
            requires: ['acme/absent'],
            onBoot: static function () use (&$booted) { $booted = true; },
        ));

        $manager->load(new AdminPanel());

        self::assertFalse($booted);
        self::assertStringContainsString('acme/absent', $manager->failures()['acme/child']);
    }

    public function test_a_plugin_whose_dependency_failed_is_skipped_too(): void
    {
        $booted = false;

        $manager = new PluginManager();
        $manager->add($this->plugin(
            'acme/parent',
            onRegister: static fn () => throw new RuntimeException('parent broke'),
        ));
        $manager->add($this->plugin(
            'acme/child',
            requires: ['acme/parent'],
            onBoot: static function () use (&$booted) { $booted = true; },
        ));

        $manager->load(new AdminPanel());

        self::assertFalse($booted, 'a plugin whose dependency failed cannot be sound');
    }

    public function test_a_dependency_cycle_is_reported_rather_than_ordered_arbitrarily(): void
    {
        // There is no correct order, and picking one hides the mistake.
        $manager = new PluginManager();
        $manager->add($this->plugin('acme/a', requires: ['acme/b']));
        $manager->add($this->plugin('acme/b', requires: ['acme/a']));

        $manager->load(new AdminPanel());

        $failures = $manager->failures();

        self::assertNotSame([], $failures);
        self::assertStringContainsStringIgnoringCase('circular', implode(' ', $failures));
    }

    public function test_adding_a_plugin_after_boot_is_refused(): void
    {
        // It would never be registered, and silently doing nothing is worse
        // than saying so.
        $manager = new PluginManager();
        $manager->load(new AdminPanel());

        $this->expectException(RuntimeException::class);

        $manager->add($this->plugin('acme/late'));
    }

    public function test_loading_twice_does_not_boot_anything_twice(): void
    {
        $boots = 0;

        $manager = new PluginManager();
        $manager->add($this->plugin(
            'acme/blog',
            onBoot: static function () use (&$boots) { $boots++; },
        ));

        $panel = new AdminPanel();
        $manager->load($panel);
        $manager->load($panel);

        self::assertSame(1, $boots);
    }

    public function test_a_plugin_can_register_resources_on_the_panel(): void
    {
        $panel = new AdminPanel();

        $manager = new PluginManager();
        $manager->add($this->plugin(
            'acme/blog',
            onRegister: static function (AdminPanel $panel) {
                $panel->registerNavigation([['label' => 'Blog', 'url' => '/admin/blog']]);
            },
        ));

        $manager->load($panel);

        self::assertNotSame([], $panel->getNavigation());
    }
}
