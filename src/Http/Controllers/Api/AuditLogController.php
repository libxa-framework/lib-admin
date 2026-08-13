<?php

declare(strict_types=1);

namespace Libxa\Admin\Http\Controllers\Api;

use Libxa\Admin\Auth\AdminGuard;
use Libxa\Admin\Authorization\AuthorizesRequests;
use Libxa\Admin\Authorization\Permission;
use Libxa\Atlas\DB;
use Libxa\Http\Response;

/**
 * Read the admin audit trail.
 *
 * Both methods were stubs returning an empty array with a TODO, which read as
 * "there is nothing to show" rather than "this was never written" — and since
 * nothing wrote to `audit_logs` either, the empty response was indisputably
 * correct and stayed that way through three releases.
 */
class AuditLogController
{
    use AuthorizesRequests;

    public function __construct(
        protected AdminGuard $auth,
    ) {
    }

    private const MAX_PER_PAGE = 100;

    private const DEFAULT_PER_PAGE = 25;

    public function index(): Response
    {
        // The trail records who deleted what, which makes it the last place
        // an account with limited rights should be able to read freely.
        if (($denied = $this->authorize(Permission::AUDIT_VIEW)) !== null) {
            return $denied;
        }

        $perPage = $this->perPage();
        $page = max(1, (int) (request()?->input('page') ?? 1));

        $query = DB::table('audit_logs');
        $count = DB::table('audit_logs');

        // Filters, each applied to both queries so the total matches the rows.
        foreach (['event' => 'event', 'resource_type' => 'resource_type', 'admin_user_id' => 'admin_user_id'] as $parameter => $column) {
            $value = request()?->input($parameter);

            if (is_string($value) && $value !== '') {
                $query = $query->where($column, $value);
                $count = $count->where($column, $value);
            }
        }

        $total = $count->count();

        $rows = $query
            ->orderBy('id', 'desc')
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return response()->json([
            'data' => array_map([$this, 'present'], $rows),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    public function show(string $id): Response
    {
        if (($denied = $this->authorize(Permission::AUDIT_VIEW)) !== null) {
            return $denied;
        }

        if (! ctype_digit($id)) {
            return response()->json(['message' => 'Not found.'])->withStatus(404);
        }

        $row = DB::table('audit_logs')->where('id', (int) $id)->first();

        if ($row === null) {
            return response()->json(['message' => 'Not found.'])->withStatus(404);
        }

        return response()->json(['data' => $this->present($row)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(object $row): array
    {
        $entry = (array) $row;

        // Stored as JSON text; decoded here so a client is not asked to parse
        // a string out of a JSON document it already parsed.
        foreach (['old_values', 'new_values'] as $key) {
            $entry[$key] = is_string($entry[$key] ?? null)
                ? json_decode($entry[$key], true)
                : null;
        }

        return $entry;
    }

    private function perPage(): int
    {
        $requested = (int) (request()?->input('per_page') ?? self::DEFAULT_PER_PAGE);

        if ($requested < 1) {
            return self::DEFAULT_PER_PAGE;
        }

        // Capped, so `?per_page=100000` cannot be used to pull the whole
        // trail into memory in one request.
        return min($requested, self::MAX_PER_PAGE);
    }
}
