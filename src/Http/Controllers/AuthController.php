<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Admin\Audit\AdminAudit;
use Libxa\Admin\Auth\AdminGuard;
use Libxa\Http\Request;
use Libxa\Http\Response;

class AuthController
{
    private AdminAudit $audit;

    public function __construct(
        protected AdminGuard $auth
    ) {
        $this->audit = new AdminAudit($auth);
    }

    public function login(Request $request): Response
    {
        $credentials = $request->only(['email', 'password']);
        $remember = $request->input('remember') === 'on';

        if ($this->auth->attempt($credentials, $remember)) {
            $this->audit->record('auth.login');

            return redirect('/admin/dashboard');
        }

        // Failures are recorded too, and are the more interesting half: a run
        // of them against one address is what a brute-force attempt looks
        // like from here. The email is kept because it is the only thing that
        // makes the pattern visible; the password is not touched.
        $this->audit->record('auth.login_failed', null, null, null, [
            'email' => is_string($credentials['email'] ?? null) ? $credentials['email'] : null,
        ]);

        return redirect('/admin/login')
            ->with('error', 'Invalid credentials');
    }

    public function logout(Request $request): Response
    {
        // Recorded before the guard forgets who it was.
        $this->audit->record('auth.logout');

        $this->auth->logout();

        return redirect('/admin/login');
    }
}
