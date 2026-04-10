<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Middleware;

use Libxa\Http\Request;
use Libxa\Http\Response;
use Libxa\Admin\Auth\AdminGuard;

class RedirectIfAuthenticated
{
    public function __construct(
        protected AdminGuard $auth
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        if ($this->auth->check()) {
            return redirect('/admin/dashboard');
        }

        return $next($request);
    }
}
