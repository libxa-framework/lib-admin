<?php

declare(strict_types=1);

namespace Libxa\Admin\Authorization;

use Libxa\Atlas\DB;

/**
 * The roles a panel starts with, and how they are written to the database.
 *
 * `--role superadmin` on `admin:make-user` used to warn that the role did not
 * exist, because nothing ever created one. A panel whose access-control tables
 * are empty on a fresh install has access control that nobody will turn on.
 */
final class Roles
{
    public const SUPERADMIN = 'superadmin';

    public const ADMIN = 'admin';

    public const EDITOR = 'editor';

    public const VIEWER = 'viewer';

    /**
     * @return array<string, array{label: string, description: string}>
     */
    public static function defaults(): array
    {
        return [
            self::SUPERADMIN => [
                'label' => 'Super administrator',
                'description' => 'Everything, including resources added later. Not granted per permission — this role is checked by name, so a new resource is covered without re-syncing.',
            ],
            self::ADMIN => [
                'label' => 'Administrator',
                'description' => 'Full access to every resource and to media, but the grants are explicit, so a new resource has to be granted.',
            ],
            self::EDITOR => [
                'label' => 'Editor',
                'description' => 'Read, create and edit. Cannot delete, and cannot read the audit trail.',
            ],
            self::VIEWER => [
                'label' => 'Viewer',
                'description' => 'Read-only.',
            ],
        ];
    }

    /**
     * The abilities each role gets on every resource.
     *
     * Superadmin is absent on purpose: it is checked by name in the Gate. A
     * role that had to hold every permission would silently lose access to
     * each resource added after the last sync — which is the same failure as
     * having no superadmin at all, arriving quietly and much later.
     *
     * @return array<string, list<string>>
     */
    public static function resourceAbilities(): array
    {
        return [
            self::ADMIN => ['viewAny', 'view', 'create', 'update', 'delete', 'export', 'import'],
            self::EDITOR => ['viewAny', 'view', 'create', 'update', 'export'],
            self::VIEWER => ['viewAny', 'view'],
        ];
    }

    /**
     * The panel-wide permissions each role gets.
     *
     * @return array<string, list<string>>
     */
    public static function panelPermissions(): array
    {
        return [
            self::ADMIN => [
                Permission::MEDIA_VIEW,
                Permission::MEDIA_UPLOAD,
                Permission::MEDIA_DELETE,
                Permission::AUDIT_VIEW,
            ],
            self::EDITOR => [
                Permission::MEDIA_VIEW,
                Permission::MEDIA_UPLOAD,
            ],
            self::VIEWER => [
                Permission::MEDIA_VIEW,
            ],
        ];
    }

    /**
     * Create any default role that does not exist yet.
     *
     * Existing rows are left alone: a label someone has edited is theirs, and
     * re-running this must not undo it.
     *
     * @return list<string> the roles created
     */
    public static function seed(): array
    {
        $created = [];

        foreach (self::defaults() as $name => $meta) {
            if (self::find($name) !== null) {
                continue;
            }

            DB::table('roles')->insert([
                'name' => $name,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $created[] = $name;
        }

        return $created;
    }

    public static function find(string $name): ?object
    {
        return DB::table('roles')->where('name', $name)->first();
    }

    /**
     * Attach a role to an admin, once.
     *
     * There is no unique index on role_user, so without this check assigning
     * twice produces two rows — harmless for a permission check, and confusing
     * in every listing.
     */
    public static function attach(int $roleId, int $adminUserId): bool
    {
        $existing = DB::table('role_user')
            ->where('role_id', $roleId)
            ->where('admin_user_id', $adminUserId)
            ->first();

        if ($existing !== null) {
            return false;
        }

        DB::table('role_user')->insert([
            'role_id' => $roleId,
            'admin_user_id' => $adminUserId,
        ]);

        return true;
    }

    public static function detach(int $roleId, int $adminUserId): bool
    {
        $existing = DB::table('role_user')
            ->where('role_id', $roleId)
            ->where('admin_user_id', $adminUserId)
            ->first();

        if ($existing === null) {
            return false;
        }

        DB::table('role_user')
            ->where('role_id', $roleId)
            ->where('admin_user_id', $adminUserId)
            ->delete();

        return true;
    }

    /** How many admins hold a role. */
    public static function holderCount(int $roleId): int
    {
        return DB::table('role_user')->where('role_id', $roleId)->count();
    }
}
