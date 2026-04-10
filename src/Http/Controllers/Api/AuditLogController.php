<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers\Api;

use Libxa\Http\Response;

class AuditLogController
{
    public function index(): Response
    {
        // TODO: Implement index logic
        return response()->json([
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'per_page' => 25,
                'total' => 0,
                'last_page' => 1,
            ],
        ]);
    }

    public function show(string $id): Response
    {
        // TODO: Implement show logic
        return response()->json([
            'data' => [],
        ]);
    }
}
