<?php

declare(strict_types=1);

namespace Libxa\Admin\Pages;

abstract class Page
{
    /** @var string The blade view that renders this page */
    protected static string|null $view = null;
    
    /** @var string Allows developers to skip the default layout completely */
    protected static string $layout = 'admin::layouts.admin';
    
    /** @var string The navigation label */
    protected static string|null $navigationLabel = null;
    
    /** @var string The URL slug for this page */
    protected static string|null $slug = null;
    
    /** @var string Material symbol icon */
    protected static string $icon = 'document';
    
    /** @var string Sidebar grouping */
    protected static string|null $navigationGroup = 'General';

    public static function getView(): string|null
    {
        return static::$view;
    }

    public static function getLayout(): string
    {
        return static::$layout;
    }

    public static function getNavigationLabel(): string|null
    {
        return static::$navigationLabel ?? class_basename(static::class);
    }

    public static function getSlug(): string|null
    {
        return static::$slug ?? strtolower(class_basename(static::class));
    }

    public static function getIcon(): string
    {
        return static::$icon;
    }

    public static function getNavigationGroup(): string|null
    {
        return static::$navigationGroup;
    }

    /**
     * Data passed to the view.
     */
    protected function getViewData(): array
    {
        return [];
    }

    public function render(): string
    {
        return app('blade')->render(
            'admin::pages.custom', 
            ['page' => $this, 'view' => static::getView(), 'data' => $this->getViewData()]
        );
    }
}
