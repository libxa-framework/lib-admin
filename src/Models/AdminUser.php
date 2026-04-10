<?php

declare(strict_types=1);

namespace Libxa\Admin\Models;

use Libxa\Auth\Authenticatable;
use Libxa\Atlas\DB;

class AdminUser implements Authenticatable
{
    protected ?object $data = null;

    public function __construct(?object $data = null)
    {
        $this->data = $data;
    }

    public static function find(int $id): ?self
    {
        $data = DB::table('admin_users')->where('id', $id)->first();
        return $data ? new self($data) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $data = DB::table('admin_users')->where('email', $email)->first();
        return $data ? new self($data) : null;
    }

    public static function create(array $data): self
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $id = DB::table('admin_users')->insert($data);
        return self::find($id);
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->data?->id;
    }

    public function getAuthPassword(): string
    {
        return $this->data?->password ?? '';
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function getRememberToken(): ?string
    {
        return $this->data?->remember_token ?? null;
    }

    public function setRememberToken(string $token): void
    {
        if ($this->data) {
            $this->data->remember_token = $token;
            DB::table('admin_users')->where('id', $this->data->id)->updateRecord(['remember_token' => $token]);
        }
    }

    public function withAccessToken(mixed $token): self
    {
        // For admin panel, we don't use API tokens, so just return self
        return $this;
    }

    public function __get(string $name)
    {
        return $this->data->$name ?? null;
    }

    public function __set(string $name, $value): void
    {
        if ($this->data) {
            $this->data->$name = $value;
        }
    }
}
