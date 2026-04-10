<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Http\Request;
use Libxa\Http\Response;
use Libxa\Admin\Auth\AdminGuard;

class AuthController
{
    public function __construct(
        protected AdminGuard $auth
    ) {}

    public function login(Request $request): Response
    {
        $credentials = $request->only(['email', 'password']);
        $remember = $request->input('remember') === 'on';

        if ($this->auth->attempt($credentials, $remember)) {
            return redirect('/admin/dashboard');
        }

        return redirect('/admin/login')
            ->with('error', 'Invalid credentials');
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout();
        return redirect('/admin/login');
    }
}
