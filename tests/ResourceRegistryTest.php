<?php

declare(strict_types=1);

namespace Libxa\Admin\Tests;

use Libxa\Admin\Panel\ResourceRegistry;
use PHPUnit\Framework\TestCase;

/**
 * The guard that decides whether a URL segment may reach the database.
 *
 * This exists because it did not. The controllers took the `{resource}`
 * segment straight from the URL and used it as a table name:
 *
 *     DB::select("PRAGMA table_info($resource)");
 *     DB::table($resource)->insert($data);
 *     DB::table($resource)->where('id', $id)->delete();
 *
 * So `/admin/resources/<any table>` read, wrote and deleted any table in the
 * database, `admin_users` included, and the first line was plain SQL
 * injection. These tests pin the shape of the fix.
 */
final class ResourceRegistryTest extends TestCase
{
    // ── identifier shape ─────────────────────────────────────────────────

    /**
     * Anything that is not a bare identifier cannot be a slug, and is refused
     * before it is looked up at all.
     */
    public function test_slugs_that_could_carry_sql_are_refused(): void
    {
        $attacks = [
            "users; DROP TABLE admin_users; --",
            "users' OR '1'='1",
            'users) UNION SELECT * FROM admin_users --',
            'users`',
            'users"',
            '../../etc/passwd',
            'users admin_users',
            '',
            ' ',
            "users\nadmin_users",
            "users\0",
        ];

        foreach ($attacks as $attack) {
            self::assertNull(
                ResourceRegistry::resolve($attack),
                'this must never resolve: ' . var_export($attack, true),
            );
        }
    }

    public function test_a_slug_of_the_right_shape_but_no_registration_still_resolves_to_nothing(): void
    {
        // The shape check is not the allow-list. `admin_users` looks like a
        // perfectly good identifier, and that is exactly the table an attacker
        // wants: it has to be refused for not being registered.
        ResourceRegistry::flush();

        self::assertNull(ResourceRegistry::resolve('admin_users'));
        self::assertNull(ResourceRegistry::tableFor('admin_users'));
    }

    public function test_a_table_is_never_derived_from_the_slug(): void
    {
        // Even a well-formed slug yields no table unless a registered resource
        // provides one. The URL is not a source of table names.
        ResourceRegistry::flush();

        foreach (['posts', 'users', 'roles', 'permissions', 'media'] as $slug) {
            self::assertNull(
                ResourceRegistry::tableFor($slug),
                "[{$slug}] must not resolve to a table without a registered resource",
            );
        }
    }

    // ── column safety ────────────────────────────────────────────────────

    public function test_a_column_used_as_an_identifier_must_be_a_bare_identifier(): void
    {
        // Sort and filter parameters arrive from the query string and cannot
        // be bound as parameters, so their shape is the only thing standing
        // between them and the query.
        $attacks = [
            'id; DROP TABLE users',
            '(SELECT password FROM admin_users)',
            'id, password',
            'id`',
            "id' --",
            '1=1',
            '',
        ];

        foreach ($attacks as $attack) {
            self::assertFalse(
                ResourceRegistry::isSafeColumn($attack),
                'this must never be used as a column: ' . var_export($attack, true),
            );
        }
    }

    public function test_a_plain_column_name_is_accepted(): void
    {
        foreach (['id', 'created_at', 'user_id', 'Title', '_private', 'a1'] as $column) {
            self::assertTrue(ResourceRegistry::isSafeColumn($column), $column);
        }
    }

    public function test_a_column_can_be_restricted_to_a_known_list(): void
    {
        // Shape alone still allows any column in the table, including one the
        // resource never exposes. An allow-list narrows it to what the
        // resource actually has.
        self::assertTrue(ResourceRegistry::isSafeColumn('title', ['title', 'body']));
        self::assertFalse(ResourceRegistry::isSafeColumn('password', ['title', 'body']));
    }

    public function test_a_column_starting_with_a_digit_is_refused(): void
    {
        // Not valid as an unquoted identifier, so accepting it would mean
        // quoting, and quoting is where escaping bugs live.
        self::assertFalse(ResourceRegistry::isSafeColumn('1column'));
    }

    // ── slugs ────────────────────────────────────────────────────────────

    public function test_a_slug_is_derived_from_the_resource_not_the_request(): void
    {
        $resource = new class () {
            public static function getPluralLabel(): string
            {
                return 'Blog Posts';
            }
        };

        self::assertSame('blog_posts', ResourceRegistry::slugFor($resource::class));
    }
}
