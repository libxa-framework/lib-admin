<?php

declare(strict_types=1);

namespace Libxa\Admin\Widgets;

/**
 * Thin wrapper returned by Widget::view() that bundles a view name + data
 * together so render() can distinguish it from a plain array.
 */
final class WidgetView
{
    public function __construct(
        public readonly string $view,
        public readonly array  $data = [],
    ) {}
}

abstract class Widget
{
    /** @var int The grid column span for this widget (1–12) */
    protected int $columnSpan = 1;

    /** @var int The sort order for this widget on the dashboard */
    protected int $sort = 0;

    /** @var string The blade view that renders this widget (static default) */
    protected static string $view = '';

    public function getColumnSpan(): int
    {
        return $this->columnSpan;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getView(): string
    {
        return static::$view;
    }

    // ─────────────────────────────────────────────────────────────────
    //  View helper — use inside getViewData() to override view + data
    // ─────────────────────────────────────────────────────────────────

    /**
     * Return a WidgetView wrapper from getViewData() to dynamically
     * control both the Blade view name and the data it receives.
     *
     * Usage inside a widget:
     *
     *   protected function getViewData(): array|WidgetView
     *   {
     *       return $this->view('admin.widgets.revenue', [
     *           'total'    => 15_000,
     *           'currency' => 'USD',
     *       ]);
     *   }
     */
    protected function view(string $view, array $data = []): WidgetView
    {
        return new WidgetView($view, $data);
    }

    /**
     * Data to pass to the static $view.
     * Override and return $this->view('some.view', [...]) for a dynamic view.
     *
     * @return array|WidgetView
     */
    protected function getViewData(): array|WidgetView
    {
        return [];
    }

    /**
     * Render the widget as an HTML string.
     * Automatically resolves WidgetView responses from getViewData().
     */
    public function render(): string
    {
        $viewData = $this->getViewData();

        if ($viewData instanceof WidgetView) {
            return app('blade')->render($viewData->view, $viewData->data);
        }

        return app('blade')->render($this->getView(), $viewData);
    }
}
