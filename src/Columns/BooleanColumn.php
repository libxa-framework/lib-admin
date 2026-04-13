<?php

declare(strict_types=1);

namespace Libxa\Admin\Columns;

/**
 * BooleanColumn — displays a check or cross icon based on a boolean value.
 */
class BooleanColumn extends AdminColumn
{
    protected string $trueIcon = 'check_circle';
    protected string $falseIcon = 'cancel';
    protected string $trueColor = 'emerald';
    protected string $falseColor = 'error';

    public function trueIcon(string $icon): static
    {
        $this->trueIcon = $icon;
        return $this;
    }

    public function falseIcon(string $icon): static
    {
        $this->falseIcon = $icon;
        return $this;
    }

    public function icons(string $true, string $false): static
    {
        $this->trueIcon = $true;
        $this->falseIcon = $false;
        return $this;
    }

    public function view(): string
    {
        return 'admin::columns.boolean';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'trueIcon' => $this->trueIcon,
            'falseIcon' => $this->falseIcon,
            'trueColor' => $this->trueColor,
            'falseColor' => $this->falseColor,
        ]);
    }
}
