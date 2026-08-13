<?php

declare(strict_types=1);

namespace Libxa\Admin\Tests;

use Libxa\Admin\Columns\AdminColumn;
use Libxa\Admin\Columns\BadgeColumn;
use Libxa\Admin\Columns\BooleanColumn;
use Libxa\Admin\Columns\TextColumn;
use PHPUnit\Framework\TestCase;

/**
 * How a column turns a record into something to display.
 *
 * Two bugs sat here for a long time without a test to catch either. The
 * resource table rendered every column as plain text inline, so BadgeColumn,
 * BooleanColumn and ImageColumn all came out as raw values and their view
 * partials were never included by anything. And formatUsing() was stored,
 * passed to the view, and then never applied — so dateTime(), which is built
 * on it, did nothing at all.
 */
final class ColumnRenderingTest extends TestCase
{
    private function record(array $attributes): object
    {
        return (object) $attributes;
    }

    // ── the formatter is actually applied ────────────────────────────────

    public function test_a_column_without_a_formatter_returns_the_raw_value(): void
    {
        $column = TextColumn::make('email')->toArray();

        self::assertSame(
            'ada@example.com',
            AdminColumn::valueFor($column, $this->record(['email' => 'ada@example.com'])),
        );
    }

    public function test_a_formatter_is_applied(): void
    {
        $column = TextColumn::make('name')
            ->formatUsing(fn ($value) => strtoupper((string) $value))
            ->toArray();

        self::assertSame('ADA', AdminColumn::valueFor($column, $this->record(['name' => 'ada'])));
    }

    public function test_a_formatter_receives_the_whole_record(): void
    {
        // So a column can be derived from more than its own value.
        $column = TextColumn::make('first')
            ->formatUsing(fn ($value, $item) => $value . ' ' . $item->last)
            ->toArray();

        self::assertSame(
            'Ada Lovelace',
            AdminColumn::valueFor($column, $this->record(['first' => 'Ada', 'last' => 'Lovelace'])),
        );
    }

    public function test_date_time_formats_the_value(): void
    {
        // dateTime() is built on formatUsing(), so it was dead for the same
        // reason: timestamps rendered exactly as the database stored them.
        $column = TextColumn::make('created_at')->dateTime('Y-m-d')->toArray();

        self::assertSame(
            '2026-08-13',
            AdminColumn::valueFor($column, $this->record(['created_at' => '2026-08-13 02:43:34'])),
        );
    }

    public function test_a_missing_column_is_null_rather_than_a_warning(): void
    {
        $column = TextColumn::make('nickname')->toArray();

        self::assertNull(AdminColumn::valueFor($column, $this->record(['email' => 'ada@example.com'])));
    }

    // ── each column names its own partial ────────────────────────────────

    public function test_every_column_type_declares_a_namespaced_view(): void
    {
        // Views are registered under the `admin` namespace. The base class
        // returned a dotted name, which resolves to nothing — harmless only
        // for as long as nothing looked it up.
        foreach ([TextColumn::make('a'), BadgeColumn::make('b'), BooleanColumn::make('c')] as $column) {
            self::assertStringStartsWith('admin::columns.', $column->view());
        }
    }

    public function test_the_view_is_carried_in_the_serialised_column(): void
    {
        // The table renders from toArray(), so a view missing from it means
        // the table cannot dispatch to the right partial.
        self::assertSame('admin::columns.badge', BadgeColumn::make('status')->toArray()['view']);
        self::assertSame('admin::columns.text', TextColumn::make('email')->toArray()['view']);
    }

    public function test_a_badge_colour_map_is_keyed_by_the_formatted_value(): void
    {
        // The point of formatting a badge: unsubscribed_at is a timestamp or
        // null, and neither is something a colour map can be keyed on.
        $column = BadgeColumn::make('unsubscribed_at')
            ->formatUsing(fn ($value) => $value === null ? 'subscribed' : 'unsubscribed')
            ->colors(['subscribed' => 'emerald', 'unsubscribed' => 'slate'])
            ->toArray();

        self::assertSame('subscribed', AdminColumn::valueFor($column, $this->record(['unsubscribed_at' => null])));
        self::assertSame('unsubscribed', AdminColumn::valueFor($column, $this->record(['unsubscribed_at' => '2026-01-01 00:00:00'])));
        self::assertSame(['subscribed' => 'emerald', 'unsubscribed' => 'slate'], $column['colors']);
    }
}
