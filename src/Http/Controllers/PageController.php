<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Http\Response;
use Libxa\Admin\Auth\AdminGuard;
use Libxa\Admin\Facades\Admin;

class PageController
{
    public function __construct(
        protected AdminGuard $auth
    ) {}

    public function show(string $slug): Response|string
    {
        $pageClass = Admin::getPage($slug);
        
        if (!$pageClass) {
            return Response::make("Page not found", 404);
        }

        /** @var \Libxa\Admin\Pages\Page $page */
        $page = new $pageClass();
        
        return $page->render();
    }
}
