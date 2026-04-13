<?php

declare(strict_types=1);

namespace Libxa\Admin\Panel;

class AdminPanel
{
    /** @var array<string, string> */
    protected array $resources = [];

    /** @var array<string, string> */
    protected array $widgets = [];

    /** @var array<string, string> */
    protected array $pages = [];

    /** @var array<string, array> */
    protected array $navigationItems = [];

    /** @var array<string, \Closure|string> */
    protected array $renderHooks = [];

    /**
     * Register Admin Resources.
     *
     * @param array<int, string> $resources Array of Resource class names
     */
    public function registerResources(array $resources): self
    {
        foreach ($resources as $resource) {
            $this->resources[$resource] = $resource;
            $this->registerNavigationFromResource($resource);
        }
        return $this;
    }

    /**
     * Get registered resources.
     *
     * @return array<string, string>
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Register Custom Pages.
     *
     * @param array<int, string> $pages Array of Page class names
     */
    public function registerPages(array $pages): self
    {
        foreach ($pages as $page) {
            /** @var \Libxa\Admin\Pages\Page $page */
            $slug = $page::getSlug();
            $this->pages[$slug] = $page;
            
            $this->navigationItems[] = [
                'icon'  => $page::getIcon(),
                'label' => $page::getNavigationLabel(),
                'href'  => '/admin/pages/' . $slug,
                'group' => $page::getNavigationGroup(),
            ];
        }
        return $this;
    }

    /**
     * Get a registered page by slug.
     */
    public function getPage(string $slug): ?string
    {
        return $this->pages[$slug] ?? null;
    }

    /**
     * Register Dashboard Widgets.
     *
     * @param array<int, string> $widgets Array of Widget class names
     */
    public function registerWidgets(array $widgets): self
    {
        foreach ($widgets as $widget) {
            $this->widgets[] = $widget;
        }
        return $this;
    }

    /**
     * Get instance arrays of widgets.
     *
     * @return array<int, object>
     */
    public function getWidgets(): array
    {
        return array_map(fn($class) => new $class(), $this->widgets);
    }

    /**
     * Register custom navigation items.
     *
     * @param array $items
     */
    public function registerNavigation(array $items): self
    {
        foreach ($items as $item) {
            $this->navigationItems[] = $item;
        }
        return $this;
    }

    /**
     * Extract navigation info automatically from a resource.
     *
     * @param string $resourceClass
     */
    protected function registerNavigationFromResource(string $resourceClass): void
    {
        /** @var \Libxa\Admin\Resources\AdminResource $resourceClass */
        $table = str_replace(' ', '_', strtolower($resourceClass::getPluralLabel()));
        
        $this->navigationItems[] = [
            'icon'  => $resourceClass::getIcon(),
            'label' => $resourceClass::getPluralLabel(),
            'href'  => '/admin/resources/' . $table,
            'group' => $resourceClass::getGroup(),
        ];
    }

    /**
     * Get consolidated navigation array.
     */
    public function getNavigation(): array
    {
        return $this->navigationItems;
    }

    /**
     * Register a render hook.
     *
     * @param string $name
     * @param \Closure|string $content
     */
    public function registerRenderHook(string $name, \Closure|string $content): self
    {
        $this->renderHooks[$name] = $content;
        return $this;
    }

    /**
     * Render the given hook if registered.
     */
    public function renderHook(string $name): string
    {
        if (!isset($this->renderHooks[$name])) {
            return '';
        }

        $hook = $this->renderHooks[$name];
        
        if ($hook instanceof \Closure) {
            $result = $hook();
            
            if ($result instanceof \Libxa\Http\Response) {
                return $result->getContent();
            }
            
            return (string) $result;
        }
        
        return $hook;
    }
}
