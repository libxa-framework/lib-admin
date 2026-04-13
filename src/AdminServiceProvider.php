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
    }

    public function boot(): void
    {
        // 1. Load Routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php', prefix: 'admin');
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php', prefix: 'api/admin');

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
        error_log("AdminServiceProvider: Declaring publishables from $base to $app");

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
}
