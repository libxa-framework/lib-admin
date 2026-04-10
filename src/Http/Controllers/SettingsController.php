<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Http\Request;
use Libxa\Http\Response;

class SettingsController
{
    public function index(): Response
    {
        return view('admin::settings');
    }

    public function update(Request $request): Response
    {
        // TODO: Implement update logic
        return back()->with('success', 'Settings updated successfully');
    }
}
