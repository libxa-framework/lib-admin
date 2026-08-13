<?php

declare(strict_types=1);

namespace Libxa\Admin\Authorization;

use Libxa\Atlas\DB;
use Throwable;

/**
 * Whether an admin may do a thing.
 *
 * The roles, permissions, role_user and permission_role tables shipped from
 * the first release and nothing ever read them. Access control was
 * authentication-only: any account that could log in could create, edit and
 * delete every record of every registered resource, and `--role` on
 * `admin:make-user` was decoration.
 *
 * Two decisions worth stating, because both cut the other way from the
 * convenient default:
 *
 *   1. **An unknown answer is a denial.** If the tables are missing, or a
 *      query fails, `allows()` returns false. The opposite — failing open so
 *      as not to lock anyone out — turns a broken migration into an
 *      unprotected panel, and does it silently.
 *
 *   2. **A user with no roles can do nothing.** Not "everything, because
 *      nobody has configured this yet". The bootstrap problem that creates is
 *      solved at the other end, by `admin:make-user` seeding the default roles
 *      and attaching superadmin to the first account.
 */
class Gate
{
    /**
     * The role that is allowed everything.
     *
     * Checked by name rather than by holding every permission, so a resource
     * added after the role was granted is covered without re-syncing.
     */
    public const SUPERADMIN = 'superadmin';

    /**
     * Permission names by admin user id, for the life of one request.
     *
     * A page renders many checks — one per row action, one per nav item — and
     * without this each one is two joins.
     *
     * @var array<int, list<string>>
     */
    private array $cache = [];

    /** Does this admin hold this permission? */
    public function allows(?int $adminUserId, string $permission): bool
    {
        if ($adminUserId === null) {
            return false;
        }

        if ($this->isSuperadmin($adminUserId)) {
            return true;
        }

        return in_array($permission, $this->permissionsFor($adminUserId), true);
    }

    public function denies(?int $adminUserId, string $permission): bool
    {
        return ! $this->allows($adminUserId, $permission);
    }

    /** Does this admin hold at least one of these? */
    public function allowsAny(?int $adminUserId, string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->allows($adminUserId, $permission)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperadmin(?int $adminUserId): bool
    {
        return $adminUserId !== null && in_array(self::SUPERADMIN, $this->rolesFor($adminUserId), true);
    }

    /**
     * The role names held by an admin.
     *
     * @return list<string>
     */
    public function rolesFor(int $adminUserId): array
    {
        try {
            $rows = DB::select(
                'SELECT r.name
                 FROM roles r
                 INNER JOIN role_user ru ON ru.role_id = r.id
                 WHERE ru.admin_user_id = ?',
                [$adminUserId],
            );
        } catch (Throwable) {
            // See the note above: unknown is a denial, not a pass.
            return [];
        }

        return array_values(array_map(static fn (array $row): string => (string) $row['name'], $rows));
    }

    /**
     * Every permission an admin holds, through any of their roles.
     *
     * @return list<string>
     */
    public function permissionsFor(int $adminUserId): array
    {
        if (isset($this->cache[$adminUserId])) {
            return $this->cache[$adminUserId];
        }

        try {
            $rows = DB::select(
                'SELECT DISTINCT p.name
                 FROM permissions p
                 INNER JOIN permission_role pr ON pr.permission_id = p.id
                 INNER JOIN role_user ru ON ru.role_id = pr.role_id
                 WHERE ru.admin_user_id = ?',
                [$adminUserId],
            );
        } catch (Throwable) {
            return $this->cache[$adminUserId] = [];
        }

        return $this->cache[$adminUserId] = array_values(
            array_map(static fn (array $row): string => (string) $row['name'], $rows),
        );
    }

    /**
     * Forget what is cached.
     *
     * Roles change while a request is running — `admin:assign-role` in a
     * long-lived worker, a test granting then revoking — and a stale allow is
     * the more dangerous half of a stale answer.
     */
    public function forget(?int $adminUserId = null): void
    {
        if ($adminUserId === null) {
            $this->cache = [];

            return;
        }

        unset($this->cache[$adminUserId]);
    }
}
