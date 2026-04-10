<?php

declare(strict_types=1);

namespace Libxa\Admin\Auth;

use Libxa\Auth\Guard;
use Libxa\Auth\Authenticatable;
use Libxa\Session\Session;

class AdminGuard implements Guard
{
    protected ?Authenticatable $user = null;

    public function __construct(
        protected AdminUserProvider $provider,
        protected Session $session
    ) {}

    public function check(): bool
    {
        return ! is_null($this->user());
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get('admin_user_id');

        if (! is_null($id)) {
            $this->user = $this->provider->retrieveById($id);
        }

        return $this->user;
    }

    public function id(): mixed
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return ! is_null($this->provider->retrieveByCredentials($credentials));
    }

    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user && $this->provider->validateCredentials($user, $credentials)) {
            $this->login($user, $remember);
            return true;
        }

        return false;
    }

    public function login(Authenticatable $user, bool $remember = false): void
    {
        $this->session->put('admin_user_id', $user->getAuthIdentifier());
        $this->session->regenerate(true);
        $this->setUser($user);
    }

    public function loginUsingId(mixed $id, bool $remember = false): ?Authenticatable
    {
        $user = $this->provider->retrieveById($id);

        if (! is_null($user)) {
            $this->login($user, $remember);
            return $user;
        }

        return null;
    }

    public function logout(): void
    {
        $this->session->forget('admin_user_id');
        $this->session->regenerate(true);
        $this->user = null;
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }
}
