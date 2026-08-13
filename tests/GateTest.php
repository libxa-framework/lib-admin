<?php

declare(strict_types=1);

namespace Libxa\Admin\Tests;

use Libxa\Admin\Authorization\Gate;
use Libxa\Admin\Authorization\Permission;
use Libxa\Admin\Authorization\Roles;
use Libxa\Atlas\Connection\ConnectionPool;
use Libxa\Atlas\DB;
use PHPUnit\Framework\TestCase;

/**
 * Whether an admin may do a thing.
 *
 * The roles, permissions, role_user and permission_role tables shipped from
 * the first release and nothing ever read them, so access control was
 * authentication-only: any account that could log in could create, edit and
 * delete every record of every registered resource.
 */
final class GateTest extends TestCase
{
    private \PDO $pdo;

    private Gate $gate;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('CREATE TABLE roles (id INTEGER PRIMARY KEY, name TEXT UNIQUE, label TEXT, description TEXT, created_at TEXT, updated_at TEXT)');
        $this->pdo->exec('CREATE TABLE permissions (id INTEGER PRIMARY KEY, name TEXT UNIQUE, label TEXT, resource TEXT, created_at TEXT, updated_at TEXT)');
        // No surrogate key on either join table, matching the migrations. A
        // fixture with an `id` column hid a query that assumed one.
        $this->pdo->exec('CREATE TABLE role_user (role_id INTEGER, admin_user_id INTEGER)');
        $this->pdo->exec('CREATE TABLE permission_role (permission_id INTEGER, role_id INTEGER)');
        $this->pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');

        ConnectionPool::getInstance()->setConnection('default', $this->pdo);

        $this->gate = new Gate();
    }

    protected function tearDown(): void
    {
        ConnectionPool::getInstance()->reset();
    }

    private function role(string $name): int
    {
        return (int) DB::table('roles')->insert(['name' => $name, 'label' => ucfirst($name)]);
    }

    private function permission(string $name): int
    {
        return (int) DB::table('permissions')->insert(['name' => $name, 'label' => $name]);
    }

    private function grant(int $roleId, int $permissionId): void
    {
        DB::table('permission_role')->insert(['role_id' => $roleId, 'permission_id' => $permissionId]);
    }

    // ── the default answer is no ─────────────────────────────────────────

    public function test_an_admin_with_no_roles_is_allowed_nothing(): void
    {
        // Not "everything, because nobody has configured this yet".
        self::assertFalse($this->gate->allows(1, 'subscribers.viewAny'));
    }

    public function test_a_guest_is_allowed_nothing(): void
    {
        self::assertFalse($this->gate->allows(null, 'subscribers.viewAny'));
    }

    public function test_a_missing_table_denies_rather_than_permits(): void
    {
        // The decision that matters most. Failing open so as not to lock
        // anyone out turns a broken migration into an unprotected panel, and
        // does it silently.
        $this->pdo->exec('DROP TABLE permission_role');

        self::assertFalse($this->gate->allows(1, 'subscribers.viewAny'));
    }

    // ── granted permissions ──────────────────────────────────────────────

    public function test_a_granted_permission_is_allowed(): void
    {
        $role = $this->role('editor');
        $this->grant($role, $this->permission('subscribers.update'));
        Roles::attach($role, 7);

        self::assertTrue($this->gate->allows(7, 'subscribers.update'));
    }

    public function test_an_ungranted_permission_is_denied(): void
    {
        $role = $this->role('editor');
        $this->grant($role, $this->permission('subscribers.update'));
        Roles::attach($role, 7);

        self::assertFalse($this->gate->allows(7, 'subscribers.delete'));
    }

    public function test_a_permission_granted_to_another_role_is_not_inherited(): void
    {
        $editor = $this->role('editor');
        $admin = $this->role('admin');

        $this->grant($admin, $this->permission('subscribers.delete'));
        Roles::attach($editor, 7);

        self::assertFalse($this->gate->allows(7, 'subscribers.delete'));
    }

    public function test_permissions_from_several_roles_are_combined(): void
    {
        $viewer = $this->role('viewer');
        $editor = $this->role('editor');

        $this->grant($viewer, $this->permission('subscribers.viewAny'));
        $this->grant($editor, $this->permission('subscribers.update'));

        Roles::attach($viewer, 7);
        Roles::attach($editor, 7);

        self::assertTrue($this->gate->allows(7, 'subscribers.viewAny'));
        self::assertTrue($this->gate->allows(7, 'subscribers.update'));
    }

    // ── superadmin ───────────────────────────────────────────────────────

    public function test_superadmin_is_allowed_everything(): void
    {
        Roles::attach($this->role(Gate::SUPERADMIN), 1);

        self::assertTrue($this->gate->allows(1, 'subscribers.delete'));
        self::assertTrue($this->gate->allows(1, Permission::AUDIT_VIEW));
    }

    public function test_superadmin_covers_a_resource_added_after_the_grant(): void
    {
        // The reason superadmin is checked by name rather than by holding
        // every permission: a role that had to hold them all would silently
        // lose access to each resource added after the last sync.
        Roles::attach($this->role(Gate::SUPERADMIN), 1);

        self::assertTrue($this->gate->allows(1, 'a_resource_that_did_not_exist_yesterday.delete'));
    }

    public function test_a_role_merely_named_like_it_is_not_superadmin(): void
    {
        Roles::attach($this->role('superadministrator'), 1);

        self::assertFalse($this->gate->allows(1, 'subscribers.delete'));
    }

    // ── caching ──────────────────────────────────────────────────────────

    public function test_a_revoked_role_stops_allowing_once_the_cache_is_cleared(): void
    {
        $role = $this->role('editor');
        $this->grant($role, $this->permission('subscribers.update'));
        Roles::attach($role, 7);

        self::assertTrue($this->gate->allows(7, 'subscribers.update'));

        Roles::detach($role, 7);
        $this->gate->forget(7);

        self::assertFalse($this->gate->allows(7, 'subscribers.update'));
    }

    public function test_forgetting_everything_clears_every_admin(): void
    {
        $role = $this->role('editor');
        $this->grant($role, $this->permission('subscribers.update'));
        Roles::attach($role, 7);

        $this->gate->allows(7, 'subscribers.update');

        DB::table('permission_role')->where('role_id', $role)->delete();
        $this->gate->forget();

        self::assertFalse($this->gate->allows(7, 'subscribers.update'));
    }

    // ── attaching ────────────────────────────────────────────────────────

    public function test_attaching_the_same_role_twice_does_not_duplicate_it(): void
    {
        $role = $this->role('editor');

        self::assertTrue(Roles::attach($role, 7));
        self::assertFalse(Roles::attach($role, 7));
        self::assertSame(1, Roles::holderCount($role));
    }

    public function test_detaching_a_role_nobody_holds_reports_that(): void
    {
        self::assertFalse(Roles::detach($this->role('editor'), 7));
    }

    public function test_seeding_is_idempotent(): void
    {
        // Run by both admin:make-user and admin:sync-permissions, so it will
        // happen more than once, and a label somebody edited is theirs.
        $first = Roles::seed();
        $second = Roles::seed();

        self::assertNotEmpty($first);
        self::assertSame([], $second);
        self::assertSame(count($first), DB::table('roles')->count());
    }

    // ── permission naming ────────────────────────────────────────────────

    public function test_a_permission_name_is_built_the_same_way_everywhere(): void
    {
        // A check spelling it `subscribers.edit` while the seeder wrote
        // `subscribers.update` does not throw. It denies everyone forever,
        // and the panel looks like it is working.
        self::assertSame('subscribers.update', Permission::for('subscribers', Permission::UPDATE));
        self::assertContains('subscribers.delete', Permission::forResource('subscribers'));
    }
}
