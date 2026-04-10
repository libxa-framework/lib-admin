<?php

declare(strict_types=1);

namespace Libxa\Admin\Resources;

abstract class AdminResource
{
    protected static string $model;
    protected static string $label;
    protected static string $pluralLabel;
    protected static string $icon = 'folder';
    protected static string $group = 'General';
    protected static string $defaultSort = 'id';
    protected static string $defaultOrder = 'asc';
    protected static int $perPage = 25;
    protected static bool $softDeletes = false;

    public static function getModel(): string
    {
        return static::$model;
    }

    public static function getLabel(): string
    {
        return static::$label;
    }

    public static function getPluralLabel(): string
    {
        return static::$pluralLabel ?? static::getLabel();
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
}
