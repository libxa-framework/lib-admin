<?php

declare(strict_types=1);

namespace Libxa\Admin\Tests;

use Libxa\Admin\Audit\AdminAudit;
use Libxa\Atlas\Connection\ConnectionPool;
use Libxa\Atlas\DB;
use PHPUnit\Framework\TestCase;

/**
 * The admin audit trail.
 *
 * `audit_logs` shipped from the first release, with two API endpoints to read
 * it, and nothing ever wrote a row — so the panel could create, edit and
 * delete any record in the application and leave nothing behind saying who
 * did it.
 */
final class AdminAuditTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec(
            'CREATE TABLE audit_logs (
                id INTEGER PRIMARY KEY,
                admin_user_id INTEGER NULL,
                event TEXT NOT NULL,
                resource_type TEXT NULL,
                resource_id INTEGER NULL,
                old_values TEXT NULL,
                new_values TEXT NULL,
                ip_address TEXT NULL,
                user_agent TEXT NULL,
                created_at TEXT NULL,
                updated_at TEXT NULL
            )'
        );

        $this->pdo->exec('CREATE TABLE widgets (id INTEGER PRIMARY KEY, name TEXT, password TEXT)');
        $this->pdo->exec("INSERT INTO widgets (name, password) VALUES ('bolt', 'hashed-secret')");

        ConnectionPool::getInstance()->setConnection('default', $this->pdo);
    }

    protected function tearDown(): void
    {
        ConnectionPool::getInstance()->reset();
    }

    private function rows(): array
    {
        return $this->pdo->query('SELECT * FROM audit_logs ORDER BY id')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function test_an_event_is_recorded(): void
    {
        (new AdminAudit())->record('resource.created', 'widgets', 1, null, ['name' => 'bolt']);

        $rows = $this->rows();

        self::assertCount(1, $rows);
        self::assertSame('resource.created', $rows[0]['event']);
        self::assertSame('widgets', $rows[0]['resource_type']);
        self::assertSame(1, (int) $rows[0]['resource_id']);
        self::assertSame(['name' => 'bolt'], json_decode($rows[0]['new_values'], true));
    }

    public function test_a_snapshot_captures_the_row_before_it_changes(): void
    {
        $audit = new AdminAudit();

        $before = $audit->snapshot('widgets', 1);

        DB::table('widgets')->where('id', 1)->updateRecord(['name' => 'nut']);

        $audit->record('resource.updated', 'widgets', 1, $before, ['name' => 'nut']);

        $row = $this->rows()[0];

        self::assertSame('bolt', json_decode($row['old_values'], true)['name']);
        self::assertSame('nut', json_decode($row['new_values'], true)['name']);
    }

    public function test_secrets_are_redacted(): void
    {
        // An audit row is read by more people than the record it came from.
        // A password hash sitting in one is an offline cracking target.
        (new AdminAudit())->record('resource.updated', 'widgets', 1, [
            'name' => 'bolt',
            'password' => 'hashed-secret',
            'remember_token' => 'abc123',
        ]);

        $old = json_decode($this->rows()[0]['old_values'], true);

        self::assertSame('bolt', $old['name']);
        self::assertSame('[redacted]', $old['password']);
        self::assertSame('[redacted]', $old['remember_token']);
    }

    public function test_a_non_numeric_resource_id_is_stored_as_null(): void
    {
        // The column is an unsigned bigint. Coercing a uuid to 0 would point
        // the entry at no record while looking exactly like one that does.
        (new AdminAudit())->record('resource.deleted', 'widgets', 'e7c1-4a2f');

        self::assertNull($this->rows()[0]['resource_id']);
    }

    public function test_an_empty_value_set_is_stored_as_null_not_an_empty_object(): void
    {
        (new AdminAudit())->record('auth.login', null, null, [], []);

        $row = $this->rows()[0];

        self::assertNull($row['old_values']);
        self::assertNull($row['new_values']);
    }

    public function test_recording_never_throws_when_the_table_is_missing(): void
    {
        // The guarantee that matters: an audit trail which can take a request
        // down with it is one that gets switched off.
        $this->pdo->exec('DROP TABLE audit_logs');

        (new AdminAudit())->record('resource.created', 'widgets', 1);

        $this->expectNotToPerformAssertions();
    }

    public function test_a_snapshot_of_a_missing_row_is_null_rather_than_an_error(): void
    {
        self::assertNull((new AdminAudit())->snapshot('widgets', 999));
    }

    public function test_a_snapshot_of_a_missing_table_is_null_rather_than_an_error(): void
    {
        self::assertNull((new AdminAudit())->snapshot('no_such_table', 1));
    }
}
