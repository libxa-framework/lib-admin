<?php

declare(strict_types=1);

namespace Libxa\Admin\Authorization;

/**
 * The names of things you can be allowed to do.
 *
 * Permissions are `<resource>.<ability>` — `subscribers.update`,
 * `media.upload`. Building the name in one place matters: a check that spells
 * it `subscribers.edit` while the seeder wrote `subscribers.update` does not
 * throw, it just denies everyone forever, and the panel looks like it is
 * working correctly.
 */
final class Permission
{
    /** What can be done to a resource. */
    public const VIEW_ANY = 'viewAny';

    public const VIEW = 'view';

    public const CREATE = 'create';

    public const UPDATE = 'update';

    public const DELETE = 'delete';

    /**
     * Every ability a resource has.
     *
     * @return list<string>
     */
    public const RESOURCE_ABILITIES = [
        self::VIEW_ANY,
        self::VIEW,
        self::CREATE,
        self::UPDATE,
        self::DELETE,
    ];

    /** Panel-wide permissions, not tied to a resource. */
    public const MEDIA_VIEW = 'media.view';

    public const MEDIA_UPLOAD = 'media.upload';

    public const MEDIA_DELETE = 'media.delete';

    public const AUDIT_VIEW = 'audit.view';

    /**
     * Everything the panel itself defines, with a readable label.
     *
     * @return array<string, string>
     */
    public static function panel(): array
    {
        return [
            self::MEDIA_VIEW => 'Browse the media library',
            self::MEDIA_UPLOAD => 'Upload media',
            self::MEDIA_DELETE => 'Delete media',
            self::AUDIT_VIEW => 'Read the audit trail',
        ];
    }

    /** The permission name for an ability on a resource. */
    public static function for(string $resource, string $ability): string
    {
        return $resource . '.' . $ability;
    }

    /**
     * Every permission a resource implies.
     *
     * @return list<string>
     */
    public static function forResource(string $resource): array
    {
        return array_map(
            static fn (string $ability): string => self::for($resource, $ability),
            self::RESOURCE_ABILITIES,
        );
    }

    /** A readable label for a generated permission name. */
    public static function label(string $permission): string
    {
        $panel = self::panel();

        if (isset($panel[$permission])) {
            return $panel[$permission];
        }

        [$resource, $ability] = array_pad(explode('.', $permission, 2), 2, '');

        $verb = match ($ability) {
            self::VIEW_ANY => 'List',
            self::VIEW => 'View a single',
            self::CREATE => 'Create',
            self::UPDATE => 'Edit',
            self::DELETE => 'Delete',
            default => ucfirst($ability),
        };

        return $verb . ' ' . str_replace('_', ' ', $resource);
    }
}
