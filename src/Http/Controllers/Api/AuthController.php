<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers\Api;

use Libxa\Http\Request;
use Libxa\Http\Response;

class AuthController
{
    public function login(Request $request): Response
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // TODO: Implement API authentication logic
        return response()->json([
            'token' => 'your-token-here',
            'user' => [
                'id' => 1,
                'name' => 'Admin User',
                'email' => $credentials['email'],
            ],
        ]);
    }

    public function logout(Request $request): Response
    {
        // TODO: Implement logout logic
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): Response
    {
        // TODO: Return authenticated user
        return response()->json([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
    }
}
