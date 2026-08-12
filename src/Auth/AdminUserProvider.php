<?php

declare(strict_types=1);

namespace Libxa\Admin\Auth;

use Libxa\Auth\UserProvider;
use Libxa\Auth\Authenticatable;
use Libxa\Admin\Models\AdminUser;

class AdminUserProvider implements UserProvider
{
    public function retrieveById(mixed $identifier): ?Authenticatable
    {
        return AdminUser::find((int) $identifier);
    }

    public function retrieveByToken(mixed $identifier, string $token): ?Authenticatable
    {
        $user = AdminUser::find((int) $identifier);

        if (! $user) {
            return null;
        }

        $stored = (string) $user->getRememberToken();

        // hash_equals, not ===. A plain comparison returns as soon as two
        // bytes differ, so how long it takes leaks how much of the token was
        // guessed correctly, and a remember token is a credential.
        if ($stored === '' || ! hash_equals($stored, $token)) {
            return null;
        }

        return $user;
    }

    public function updateRememberToken(Authenticatable $user, string $token): void
    {
        if ($user instanceof AdminUser) {
            $user->setRememberToken($token);
        }
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials)) {
            return null;
        }

        if (isset($credentials['email'])) {
            return AdminUser::findByEmail($credentials['email']);
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (! isset($credentials['password'])) {
            return false;
        }

        return password_verify($credentials['password'], $user->getAuthPassword());
    }
}
