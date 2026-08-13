<?php

use Libxa\Router\Router;

/** @var Router $router */

/*
 * The admin JSON API.
 *
 * This file used to register thirteen routes with no middleware on any of
 * them, so the whole surface answered anybody who asked, from anywhere, with
 * no session — and twelve of the thirteen were stubs that did nothing while
 * reporting that they had:
 *
 *   POST /login   accepted any email and password without checking either,
 *                 and replied {"token": "your-token-here"} — a client built
 *                 against it believes it has authenticated somebody.
 *   GET  /me      returned a hardcoded "Admin User".
 *   /resources/*  eight methods, every one a TODO returning
 *                 "Resource created successfully" and similar.
 *
 * They are gone rather than fixed. A token-authenticated JSON CRUD API is a
 * feature to design, not a hole to patch, and nothing can be depending on the
 * behaviour of endpoints that never had any. Use the panel's own routes, or
 * write your own API against the resources — ResourceRegistry and
 * AdminResource::fields() give you the same allow-list the panel uses.
 *
 * What remains is real, and behind both authentication and a permission.
 */

$router->group(['middleware' => \Libxa\Admin\Http\Middleware\ApiAuthMiddleware::class], function ($router) {
    $router->get('/audit-logs', [\Libxa\Admin\Http\Controllers\Api\AuditLogController::class, 'index'])
        ->name('admin.api.audit-logs.index');

    $router->get('/audit-logs/{id}', [\Libxa\Admin\Http\Controllers\Api\AuditLogController::class, 'show'])
        ->name('admin.api.audit-logs.show');
});
