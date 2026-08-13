<?php

declare(strict_types=1);

namespace Libxa\Admin\Authorization;

use Libxa\Admin\Resources\AdminResource;
use Libxa\Http\Response;

/**
 * The check every admin action runs before it does anything.
 *
 * Kept to two lines at the top of an action, for the same reason the
 * resource-slug check is:
 *
 *     if (($denied = $this->authorize(...)) !== null) {
 *         return $denied;
 *     }
 *
 * An action that forgets it is an action anyone who can log in may run.
 */
trait AuthorizesRequests
{
    /**
     * Refuse the request unless the current admin holds the permission.
     *
     * Returns a Response to return, or null to carry on.
     */
    protected function authorize(string $permission): ?Response
    {
        if ($this->gate()->allows($this->currentAdminId(), $permission)) {
            return null;
        }

        return $this->forbidden();
    }

    /**
     * Refuse unless the admin may perform this ability on this resource.
     *
     * A resource with `$authorize = false` is open by declaration, which is a
     * decision someone made in its class rather than one made by forgetting.
     */
    protected function authorizeResource(AdminResource|string $resource, string $ability): ?Response
    {
        $class = is_string($resource) ? $resource : $resource::class;

        if (! $class::isAuthorized()) {
            return null;
        }

        return $this->authorize($class::permissionFor($ability));
    }

    protected function allows(string $permission): bool
    {
        return $this->gate()->allows($this->currentAdminId(), $permission);
    }

    /** Whether the current admin may perform an ability on a resource. */
    protected function allowsResource(AdminResource|string $resource, string $ability): bool
    {
        $class = is_string($resource) ? $resource : $resource::class;

        return ! $class::isAuthorized() || $this->allows($class::permissionFor($ability));
    }

    protected function gate(): Gate
    {
        $app = app();

        return $app !== null && $app->has('admin.gate')
            ? $app->make('admin.gate')
            : new Gate();
    }

    protected function currentAdminId(): ?int
    {
        // The guard is a property on every controller that uses this trait,
        // but not a contract, so this stays defensive rather than typed.
        $id = property_exists($this, 'auth') ? $this->auth?->user()?->getAuthIdentifier() : null;

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * 403, and nothing about what exists.
     *
     * Deliberately identical wording for "no such permission" and "no such
     * record", because the difference between them is a way to enumerate what
     * the panel manages.
     */
    protected function forbidden(): Response
    {
        return new Response(
            status: 403,
            headers: ['Content-Type' => 'text/html; charset=utf-8'],
            content: '<!doctype html><meta charset="utf-8"><title>Forbidden</title>'
                . '<div style="font-family:system-ui;padding:3rem;max-width:34rem;margin:0 auto">'
                . '<h1 style="font-size:1.25rem">Not allowed</h1>'
                . '<p style="color:#555">Your account does not have permission to do that.</p>'
                . '<p><a href="/admin/dashboard" style="color:#0053db">Back to the dashboard</a></p>'
                . '</div>',
        );
    }
}
