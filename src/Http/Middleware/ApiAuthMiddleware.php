<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Middleware;

use Libxa\Admin\Auth\AdminGuard;
use Libxa\Http\Request;
use Libxa\Http\Response;

/**
 * Authentication for the admin JSON API.
 *
 * The API routes had no middleware at all. Every one of them answered anybody
 * who asked, from anywhere, with no session — including the audit trail, which
 * holds the full contents of deleted records.
 *
 * This is the session guard, not a token scheme: the API exists to be called
 * by the panel's own pages, which already have a session. A 401 with a JSON
 * body rather than AdminAuthMiddleware's redirect to the login page, because
 * a caller expecting JSON gets an HTML login form and reports it as a parse
 * error somewhere far from the cause.
 */
class ApiAuthMiddleware
{
    public function __construct(
        protected AdminGuard $auth
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        if ($this->auth->check()) {
            return $next($request);
        }

        return (new Response(
            status: 401,
            headers: ['Content-Type' => 'application/json'],
            content: (string) json_encode(['message' => 'Unauthenticated.']),
        ));
    }
}
