<?php

declare(strict_types=1);

namespace Libxa\Admin\Pages;

abstract class AdminPage
{
    protected static string $path;
    protected static string $label;
    protected static string $icon = 'document';

    public static function getPath(): string
    {
        return static::$path;
    }

    public static function getLabel(): string
    {
        return static::$label;
    }

    public static function getIcon(): string
    {
        return static::$icon;
    }

    abstract public function viewData(): array;

    abstract public function view(): string;
}
