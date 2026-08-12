<?php

declare(strict_types=1);

namespace Libxa\Admin\Panel;

use Libxa\Admin\Facades\Admin;
use Libxa\Admin\Resources\AdminResource;

/**
 * Resolves a URL slug to a registered resource, and to nothing else.
 *
 * This exists because the controllers used to take the `{resource}` segment
 * from the URL and use it directly as a table name:
 *
 *     DB::select("PRAGMA table_info($resource)");
 *     DB::table($resource)->insert($filteredData);
 *
 * Which meant `/admin/resources/anything` would read and write any table in
 * the database. Not only the tables the panel manages: any of them, including
 * `admin_users`, so a visitor who could reach that route could grant
 * themselves an account. The first line is also plain SQL injection, since the
 * segment is interpolated into the query.
 *
 * The rule now is that a slug is looked up against the resources actually
 * registered, and the table comes from the resource's model rather than from
 * anything the client sent. A slug that matches nothing resolves to null, and
 * every caller turns that into a 404.
 */
final class ResourceRegistry
{
    /**
     * Table and column names this package will accept from configuration.
     *
     * Identifiers cannot be bound as parameters, so anything reaching a query
     * as an identifier has to be proven safe by shape. Sorting and filtering
     * take column names from the request, and this is what they are checked
     * against before going anywhere near SQL.
     */
    public const IDENTIFIER = '/^[A-Za-z_][A-Za-z0-9_]*$/';

    /** @var array<string, class-string<AdminResource>>|null */
    private static ?array $slugs = null;

    /**
     * The resource class for a slug, or null when nothing is registered under
     * it.
     *
     * @return class-string<AdminResource>|null
     */
    public static function resolve(string $slug): ?string
    {
        // Checked before the lookup rather than after: a slug of the wrong
        // shape cannot match anything, and refusing early keeps a value that
        // was never going to be valid from travelling any further.
        if (preg_match(self::IDENTIFIER, $slug) !== 1) {
            return null;
        }

        return self::map()[strtolower($slug)] ?? null;
    }

    /**
     * The table a resource reads and writes.
     *
     * Taken from the model, never from the URL. A resource with no model has
     * no table, and returning null makes that a 404 rather than a guess.
     */
    public static function tableFor(string $slug): ?string
    {
        $class = self::resolve($slug);

        if ($class === null) {
            return null;
        }

        $model = $class::getModel();

        if ($model === null || ! class_exists($model)) {
            return null;
        }

        $table = (new $model())->getTable();

        // The model is application code rather than user input, so this should
        // always hold. It is checked anyway: the cost is one regex, and the
        // thing being prevented is a table name reaching SQL unverified.
        return preg_match(self::IDENTIFIER, $table) === 1 ? $table : null;
    }

    /** The slug a resource is reachable at. */
    public static function slugFor(string $class): string
    {
        return strtolower(str_replace(' ', '_', (string) $class::getPluralLabel()));
    }

    /**
     * Whether a column may be used as an identifier in a query.
     *
     * For sort and filter parameters, which arrive from the query string and
     * cannot be bound. A column that is not on the resource is refused rather
     * than passed through, so `?sort=(SELECT ...)` is a 400 and not a query.
     *
     * @param list<string> $allowed
     */
    public static function isSafeColumn(string $column, array $allowed = []): bool
    {
        if (preg_match(self::IDENTIFIER, $column) !== 1) {
            return false;
        }

        return $allowed === [] || in_array($column, $allowed, true);
    }

    /** Forget the cached map. For tests, and after a plugin registers late. */
    public static function flush(): void
    {
        self::$slugs = null;
    }

    /** @return array<string, class-string<AdminResource>> */
    private static function map(): array
    {
        if (self::$slugs !== null) {
            return self::$slugs;
        }

        $map = [];

        try {
            foreach (Admin::getResources() as $class) {
                $map[self::slugFor($class)] = $class;
            }
        } catch (\Throwable) {
            // No application behind the facade, which happens in a unit test
            // and during early boot. An empty map is the safe answer: nothing
            // is registered, so nothing resolves, so nothing reaches a query.
            return self::$slugs = [];
        }

        return self::$slugs = $map;
    }
}
