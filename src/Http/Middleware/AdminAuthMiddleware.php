<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Middleware;

use Libxa\Http\Request;
use Libxa\Http\Response;
use Libxa\Admin\Auth\AdminGuard;

class AdminAuthMiddleware
{
    public function __construct(
        protected AdminGuard $auth
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        if (! $this->auth->check()) {
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
