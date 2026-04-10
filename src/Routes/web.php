<?php

use Libxa\Router\Router;

/** @var Router $router */

// Public routes (login)
$router->group(['middleware' => \Libxa\Admin\Http\Middleware\RedirectIfAuthenticated::class], function ($router) {
    $router->get('/login', function () {
        return view('admin::auth.login');
    })->name('admin.login');

    $router->post('/login', [\Libxa\Admin\Http\Controllers\AuthController::class, 'login'])
        ->name('admin.login.post');
});

// Protected routes (require authentication)
$router->group(['middleware' => \Libxa\Admin\Http\Middleware\AdminAuthMiddleware::class], function ($router) {
    $router->get('/', [\Libxa\Admin\Http\Controllers\DashboardController::class, 'index'])
        ->name('admin.home');

    $router->post('/logout', [\Libxa\Admin\Http\Controllers\AuthController::class, 'logout'])
        ->name('admin.logout');

    $router->get('/dashboard', [\Libxa\Admin\Http\Controllers\DashboardController::class, 'index'])
        ->name('admin.dashboard');

    $router->get('/resources/{resource}', [\Libxa\Admin\Http\Controllers\ResourceController::class, 'index'])
        ->name('admin.resource.index');

    $router->get('/resources/{resource}/create', [\Libxa\Admin\Http\Controllers\ResourceController::class, 'create'])
        ->name('admin.resource.create');

    $router->post('/resources/{resource}', [\Libxa\Admin\Http\Controllers\ResourceController::class, 'store'])
        ->name('admin.resource.store');

    $router->get('/resources/{resource}/{id}', [\Libxa\Admin\Http\Controllers\ResourceController::class, 'show'])
        ->name('admin.resource.show');

    $router->get('/resources/{resource}/{id}/edit', [\Libxa\Admin\Http\Controllers\ResourceController::class, 'edit'])
        ->name('admin.resource.edit');

    $router->put('/resources/{resource}/{id}', [\Libxa\Admin\Http\Controllers\ResourceController::class, 'update'])
        ->name('admin.resource.update');

    $router->delete('/resources/{resource}/{id}', [\Libxa\Admin\Http\Controllers\ResourceController::class, 'destroy'])
        ->name('admin.resource.destroy');

    // Media routes
    $router->get('/media', [\Libxa\Admin\Http\Controllers\MediaController::class, 'index'])
        ->name('admin.media.index');

    $router->post('/media', [\Libxa\Admin\Http\Controllers\MediaController::class, 'upload'])
        ->name('admin.media.upload');

    $router->delete('/media/{id}', [\Libxa\Admin\Http\Controllers\MediaController::class, 'destroy'])
        ->name('admin.media.destroy');

    // Settings routes
    $router->get('/settings', [\Libxa\Admin\Http\Controllers\SettingsController::class, 'index'])
        ->name('admin.settings');

    $router->put('/settings', [\Libxa\Admin\Http\Controllers\SettingsController::class, 'update'])
        ->name('admin.settings.update');
});
