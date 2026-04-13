<?php

declare(strict_types=1);

namespace Libxa\Admin\Resources;

abstract class AdminResource
{
    protected static string|null $model = null;
    protected static string|null $label = null;
    protected static string|null $pluralLabel = null;
    protected static string $icon = 'folder';
    protected static string $group = 'General';
    protected static string $defaultSort = 'id';
    protected static string $defaultOrder = 'asc';
    protected static int $perPage = 25;
    protected static bool $softDeletes = false;

    /** @var \Libxa\Atlas\Model|null The current model instance being processed */
    public ?\Libxa\Atlas\Model $item = null;

    public static function getModel(): string|null
    {
        return static::$model;
    }

    public static function getLabel(): string|null
    {
        return static::$label ?? str_replace('Resource', '', class_basename(static::class));
    }

    public static function getPluralLabel(): string|null
    {
        return static::$pluralLabel ?? (static::getLabel() . 's');
    }

    public static function getIcon(): string
    {
        return static::$icon;
    }

    public static function getGroup(): string
    {
        return static::$group;
    }

    public static function getDefaultSort(): string
    {
        return static::$defaultSort;
    }

    public static function getDefaultOrder(): string
    {
        return static::$defaultOrder;
    }

    public static function getPerPage(): int
    {
        return static::$perPage;
    }

    public static function hasSoftDeletes(): bool
    {
        return static::$softDeletes;
    }

    public static function permissions(): array
    {
        return ['viewAny', 'view', 'create', 'update', 'delete', 'export', 'import'];
    }

    abstract public function columns(): array;

    abstract public function fields(): array;

    public function filters(): array
    {
        return [];
    }

    public function actions(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function headerActions(): array
    {
        return [];
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }

    public function getFooterWidgets(): array
    {
        return [];
    }
}
