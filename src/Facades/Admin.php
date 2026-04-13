<?php

declare(strict_types=1);

namespace Libxa\Admin\Facades;

use Libxa\Admin\Panel\AdminPanel;

/**
 * @method static \Libxa\Admin\Panel\AdminPanel registerResources(array $resources)
 * @method static array getResources()
 * @method static \Libxa\Admin\Panel\AdminPanel registerWidgets(array $widgets)
 * @method static array getWidgets()
 * @method static \Libxa\Admin\Panel\AdminPanel registerNavigation(array $items)
 * @method static array getNavigation()
 * @method static \Libxa\Admin\Panel\AdminPanel registerRenderHook(string $name, \Closure|string $content)
 * @method static string renderHook(string $name)
 *
 * @see \Libxa\Admin\Panel\AdminPanel
 */
class Admin
{
    /** @var \Libxa\Admin\Panel\AdminPanel|null */
    protected static ?AdminPanel $resolvedInstance = null;

    protected static function resolveFacadeInstance(): AdminPanel
    {
        if (!self::$resolvedInstance) {
            // Check if bound in container, otherwise create singleton
            if (app()->has(AdminPanel::class)) {
                self::$resolvedInstance = app()->make(AdminPanel::class);
            } else {
                self::$resolvedInstance = new AdminPanel();
                app()->singleton(AdminPanel::class, fn() => self::$resolvedInstance);
            }
        }
        return self::$resolvedInstance;
    }

    public static function __callStatic(string $method, array $args)
    {
        $instance = static::resolveFacadeInstance();
        return $instance->$method(...$args);
    }
}
