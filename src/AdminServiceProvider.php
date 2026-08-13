<?php

declare(strict_types=1);

namespace Libxa\Admin;

use Libxa\Foundation\Application;
use Libxa\Foundation\ModuleServiceProvider;

class AdminServiceProvider extends ModuleServiceProvider
{
    public function register(): void
    {
        parent::register();

        // Register Admin Guard
        // Shared, because a plugin registered by one provider has to be
        // visible to the panel booted by another. A fresh manager per
        // resolution would silently drop everything registered elsewhere.
        $this->app->singleton('admin.plugins', function () {
            return new \Libxa\Admin\Plugins\PluginManager();
        });

        $this->app->alias('admin.plugins', \Libxa\Admin\Plugins\PluginManager::class);

        $this->app->singleton('admin.media', function ($app) {
            return new \Libxa\Admin\Media\MediaStore(
                storage: $app->make('storage'),
                disk: (string) $this->config('admin.media.disk', 'public'),
                directory: trim((string) $this->config('admin.media.path', 'media'), '/'),
                maxBytes: \Libxa\Admin\Media\MediaStore::parseSize(
                    $this->config('admin.media.max_file_size', '10mb'),
                ),
            );
        });

        $this->app->alias('admin.media', \Libxa\Admin\Media\MediaStore::class);

        // Shared, because it caches an admin's permissions for the life of a
        // request and a page runs one check per row action and nav item. A
        // fresh gate per resolution would make every one of them two joins.
        $this->app->singleton('admin.gate', function () {
            return new \Libxa\Admin\Authorization\Gate();
        });

        $this->app->alias('admin.gate', \Libxa\Admin\Authorization\Gate::class);

        $this->app->singleton('admin.auth', function ($app) {
            return new \Libxa\Admin\Auth\AdminGuard(
                new \Libxa\Admin\Auth\AdminUserProvider(),
                $app->make('session')
            );
        });

        // Register Admin Auth Middleware
        $this->app->singleton(\Libxa\Admin\Http\Middleware\AdminAuthMiddleware::class, function ($app) {
            return new \Libxa\Admin\Http\Middleware\AdminAuthMiddleware($app->make('admin.auth'));
        });

        $this->app->singleton(\Libxa\Admin\Http\Middleware\RedirectIfAuthenticated::class, function ($app) {
            return new \Libxa\Admin\Http\Middleware\RedirectIfAuthenticated($app->make('admin.auth'));
        });

        $this->app->singleton(\Libxa\Admin\Http\Middleware\ApiAuthMiddleware::class, function ($app) {
            return new \Libxa\Admin\Http\Middleware\ApiAuthMiddleware($app->make('admin.auth'));
        });
    }

    public function boot(): void
    {
        // 1. Load Routes
        // Read from config, not hardcoded. `admin.path` and
        // `admin.api.prefix` were both settings that did nothing: the prefix
        // was written in literally here, so changing them had no effect and
        // nothing said why.
        //
        // It also matters because the framework's own Nova module defaults to
        // the same `/admin` prefix. Two packages claiming one path means
        // whichever registers first wins, so being able to move this is the
        // difference between installing both and having to choose.
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php', prefix: $this->adminPath());
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php', prefix: $this->apiPrefix());

        // 2. Load Views
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'admin');

        // 3. Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // 4. Load Translations
        $this->loadTranslationsFrom(__DIR__ . '/Resources/lang', 'admin');

        // 5. Load Console Commands
        $this->loadCommandsFrom(__DIR__ . '/Console/Commands');

        // 6. Register Events
        $this->listen([]);

        // 7. Declare publishable assets
        $this->declarePublishables();

        // Last, so plugins see a panel with everything the package itself
        // provides already on it. The manager records and skips a plugin that
        // throws rather than letting it propagate: the panel is what you open
        // when something is already wrong, and a broken plugin must not be the
        // reason you cannot look.
        $this->loadPlugins();
    }

    public function requires(): array
    {
        return [];
    }

    protected function declarePublishables(): void
    {
        $base = __DIR__;
        $app = $this->app->basePath();

        // Debug: Log that publishables are being declared

        $this->publishes([$base . '/Database/Migrations' => $app . '/src/database/migrations'], 'admin-migrations');
        $this->publishes([$base . '/Config/admin.php' => $app . '/src/config/admin.php'], 'admin-config');
        $this->publishes([
            __DIR__ . '/../public' => base_path('public/vendor/admin'),
        ], 'admin-assets');

        // Allow publishing the Service Provider
        if (Application::getInstance()->isCli()) {
            $this->publishes([
                __DIR__ . '/../stubs/AdminPanelProvider.stub' => base_path('src/app/Providers/AdminPanelProvider.php'),
            ], 'admin-provider');
        }

        $this->publishes([$base . '/Resources/views' => $app . '/src/resources/views/vendor/admin'], 'admin-views');
        $this->publishes([$base . '/Resources/lang' => $app . '/src/lang/admin'], 'admin-lang');

        // Publish everything at once
        $this->publishes([
            $base . '/Database/Migrations' => $app . '/src/database/migrations',
            $base . '/Config/admin.php' => $app . '/src/config/admin.php',
            $base . '/Resources/lang' => $app . '/src/lang/admin',
            $base . '/Resources/views' => $app . '/src/resources/views/vendor/admin',
        ], 'admin');
    }

    /** Where the panel is served, trimmed so a leading slash cannot double it. */
    protected function adminPath(): string
    {
        return trim((string) $this->config('admin.path', 'admin'), '/');
    }

    protected function apiPrefix(): string
    {
        return trim((string) $this->config('admin.api.prefix', 'admin/api'), '/');
    }

    /** Read a config value without assuming config is bound yet. */
    protected function config(string $key, mixed $default): mixed
    {
        if (! $this->app->has('config')) {
            return $default;
        }

        $value = $this->app->make('config')->get($key, $default);

        return $value === null || $value === '' ? $default : $value;
    }

    /**
     * Hand every registered plugin to the panel.
     *
     * Wrapped because a plugin failing to load must not stop the panel from
     * loading. Anything skipped is recorded on the manager and shown in the
     * interface rather than swallowed.
     */
    protected function loadPlugins(): void
    {
        if (! $this->app->has('admin.plugins')) {
            return;
        }

        try {
            $manager = $this->app->make('admin.plugins');
            $manager->load(\Libxa\Admin\Facades\Admin::panel());
        } catch (\Throwable $e) {
            // Reported, not raised. See the note on the call site.
            if (function_exists('logger')) {
                $logger = logger();

                if (is_object($logger) && method_exists($logger, 'error')) {
                    $logger->error('LibAdmin could not load plugins: ' . $e->getMessage());
                }
            }
        }
    }
}
