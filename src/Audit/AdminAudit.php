<?php

declare(strict_types=1);

namespace Libxa\Admin\Audit;

use Libxa\Admin\Auth\AdminGuard;
use Libxa\Atlas\DB;
use Throwable;

/**
 * What happened in the panel, and who did it.
 *
 * The `audit_logs` table shipped from the first release, along with two API
 * endpoints to read it, and nothing ever wrote a row. So the panel could
 * create, edit and delete any record in the application and leave no trace of
 * who did it — which is the one question an audit trail exists to answer.
 *
 * Two rules here, both learned the hard way in `libxa/secure`:
 *
 *   1. **This never throws.** An audit trail that can take a request down with
 *      it is one that gets switched off by the first person it inconveniences.
 *      A failed write is dropped, not raised.
 *
 *   2. **The old values are read before the write, not after.** Recording an
 *      update by reading the row afterwards records the new values twice and
 *      loses the only copy of what was there before.
 */
class AdminAudit
{
    /**
     * Values never worth copying into the trail.
     *
     * A password hash in an audit row is an offline cracking target sitting in
     * a table built to be read by more people than the one it came from.
     */
    private const REDACTED = ['password', 'remember_token', 'api_token', 'secret'];

    public function __construct(private readonly ?AdminGuard $guard = null)
    {
    }

    /**
     * Record an event.
     *
     * @param array<string, mixed>|null $old
     * @param array<string, mixed>|null $new
     */
    public function record(
        string $event,
        ?string $resourceType = null,
        int|string|null $resourceId = null,
        ?array $old = null,
        ?array $new = null,
    ): void {
        try {
            DB::table('audit_logs')->insert([
                'admin_user_id' => $this->actorId(),
                'event' => $event,
                'resource_type' => $resourceType,
                // The column is an unsigned bigint, and a resource may key on
                // something else entirely; anything non-numeric is dropped
                // rather than coerced to 0, which would point at no record and
                // look like one.
                'resource_id' => is_numeric($resourceId) ? (int) $resourceId : null,
                'old_values' => $this->encode($old),
                'new_values' => $this->encode($new),
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable) {
            // See the note above: losing a row here must never lose the action.
        }
    }

    /** The row as it stands, for recording before an update or a delete. */
    public function snapshot(string $table, int|string $id): ?array
    {
        try {
            $row = DB::table($table)->where('id', $id)->first();

            return $row === null ? null : (array) $row;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed>|null $values
     */
    private function encode(?array $values): ?string
    {
        if ($values === null || $values === []) {
            return null;
        }

        foreach (array_keys($values) as $key) {
            if (in_array((string) $key, self::REDACTED, true)) {
                $values[$key] = '[redacted]';
            }
        }

        $json = json_encode($values, JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);

        return $json === false ? null : $json;
    }

    private function actorId(): ?int
    {
        try {
            $id = $this->guard?->user()?->getAuthIdentifier();

            return is_numeric($id) ? (int) $id : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function ip(): ?string
    {
        try {
            $ip = request()?->ip();

            return is_string($ip) && $ip !== '' ? $ip : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function userAgent(): ?string
    {
        try {
            $agent = request()?->header('User-Agent');
        } catch (Throwable) {
            return null;
        }

        if (! is_string($agent) || $agent === '') {
            return null;
        }

        // The column is a VARCHAR and a user agent is attacker-controlled
        // free text of any length.
        return mb_substr($agent, 0, 255);
    }
}
