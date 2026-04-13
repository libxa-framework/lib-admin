<?php

declare(strict_types=1);

namespace Libxa\Admin\Columns;

/**
 * BadgeColumn — displays colored pills/badges for status or categories.
 */
class BadgeColumn extends AdminColumn
{
    protected array $colors = []; // [ 'value' => 'color' ]

    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function status(array $statusMap): static
    {
        return $this->colors($statusMap);
    }

    public function view(): string
    {
        return 'admin::columns.badge';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'colors' => $this->colors,
        ]);
    }
}
