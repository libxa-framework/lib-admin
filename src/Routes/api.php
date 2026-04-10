<?php

use Libxa\Router\Router;

/** @var Router $router */

$router->post('/login', [\Libxa\Admin\Http\Controllers\Api\AuthController::class, 'login'])
    ->name('admin.api.login');

$router->post('/logout', [\Libxa\Admin\Http\Controllers\Api\AuthController::class, 'logout'])
    ->name('admin.api.logout');

$router->get('/me', [\Libxa\Admin\Http\Controllers\Api\AuthController::class, 'me'])
    ->name('admin.api.me');

$router->get('/resources/{resource}', [\Libxa\Admin\Http\Controllers\Api\ResourceController::class, 'index'])
    ->name('admin.api.resource.index');

$router->post('/resources/{resource}', [\Libxa\Admin\Http\Controllers\Api\ResourceController::class, 'store'])
    ->name('admin.api.resource.store');

$router->get('/resources/{resource}/{id}', [\Libxa\Admin\Http\Controllers\Api\ResourceController::class, 'show'])
    ->name('admin.api.resource.show');

$router->put('/resources/{resource}/{id}', [\Libxa\Admin\Http\Controllers\Api\ResourceController::class, 'update'])
    ->name('admin.api.resource.update');

$router->delete('/resources/{resource}/{id}', [\Libxa\Admin\Http\Controllers\Api\ResourceController::class, 'destroy'])
    ->name('admin.api.resource.destroy');

$router->post('/resources/{resource}/bulk', [\Libxa\Admin\Http\Controllers\Api\ResourceController::class, 'bulk'])
    ->name('admin.api.resource.bulk');

$router->get('/resources/{resource}/export', [\Libxa\Admin\Http\Controllers\Api\ResourceController::class, 'export'])
    ->name('admin.api.resource.export');

$router->post('/resources/{resource}/import', [\Libxa\Admin\Http\Controllers\Api\ResourceController::class, 'import'])
    ->name('admin.api.resource.import');

$router->get('/audit-logs', [\Libxa\Admin\Http\Controllers\Api\AuditLogController::class, 'index'])
    ->name('admin.api.audit-logs.index');

$router->get('/audit-logs/{id}', [\Libxa\Admin\Http\Controllers\Api\AuditLogController::class, 'show'])
    ->name('admin.api.audit-logs.show');
