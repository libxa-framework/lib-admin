<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Http\Request;
use Libxa\Http\Response;
use Libxa\Admin\Auth\AdminGuard;

class DashboardController
{
    public function __construct(
        protected AdminGuard $auth
    ) {}

    public function index(): Response
    {
        $user = $this->auth->user();
        return view('admin::dashboard', compact('user'));
    }
}
