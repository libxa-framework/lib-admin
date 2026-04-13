<?php

declare(strict_types=1);

namespace Libxa\Admin\Widgets;

abstract class StatsOverviewWidget extends Widget
{
    protected static string $view = 'admin::widgets.stats-overview';
    protected int $columnSpan = 12; // E.g., takes up the full width by default

    /**
     * @return Stat[]
     */
    abstract protected function getStats(): array;

    protected function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
        ];
    }
}
