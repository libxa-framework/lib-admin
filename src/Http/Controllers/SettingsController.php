<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers;

use Libxa\Http\Request;
use Libxa\Http\Response;

/**
 * Not routed. See the note at the end of src/Routes/web.php.
 *
 * index() rendered admin::settings, a view that does not exist, so the page
 * answered 500; update() returned "Settings updated successfully" without
 * writing anything anywhere. Both are kept here, throwing, so that anyone who
 * wires a route to this controller finds out immediately rather than shipping
 * a settings screen that quietly discards what is typed into it.
 */
class SettingsController
{
    public function index(): Response
    {
        throw new \LogicException(self::MESSAGE);
    }

    public function update(Request $request): Response
    {
        throw new \LogicException(self::MESSAGE);
    }

    private const MESSAGE = 'LibAdmin has no settings store yet. This controller is a placeholder: implement it, and register the routes, before linking to it.';
}
