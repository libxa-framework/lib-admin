<?php

declare(strict_types=1);

namespace Libxa\Admin\Columns;

/**
 * ImageColumn — displays a small image preview (avatar or thumbnail).
 */
class ImageColumn extends AdminColumn
{
    protected bool $circular = true;
    protected int $size = 32;

    public function circular(bool $circular = true): static
    {
        $this->circular = $circular;
        return $this;
    }

    public function square(): static
    {
        $this->circular = false;
        return $this;
    }

    public function size(int $px): static
    {
        $this->size = $px;
        return $this;
    }

    public function view(): string
    {
        return 'admin::columns.image';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'circular' => $this->circular,
            'size' => $this->size,
        ]);
    }
}
