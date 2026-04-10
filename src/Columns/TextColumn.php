<?php

declare(strict_types=1);

namespace Libxa\Admin\Columns;

class TextColumn extends AdminColumn
{
    protected bool $wrap = false;
    protected int $limit = 0;

    public function wrap(bool $wrap = true): static
    {
        $this->wrap = $wrap;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'wrap' => $this->wrap,
            'limit' => $this->limit,
        ]);
    }
}
