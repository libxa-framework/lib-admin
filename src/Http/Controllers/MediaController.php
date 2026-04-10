<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Http\Request;
use Libxa\Http\Response;

class MediaController
{
    public function index(): Response
    {
        return view('admin::media.index');
    }

    public function upload(Request $request): Response
    {
        // TODO: Implement upload logic
        return back()->with('success', 'File uploaded successfully');
    }

    public function destroy(string $id): Response
    {
        // TODO: Implement destroy logic
        return back()->with('success', 'File deleted successfully');
    }
}
