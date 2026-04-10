<?php

declare(strict_types=1);

namespace Libxa\Admin\Widgets;

abstract class AdminWidget
{
    protected string $title;
    protected int $span = 1;

    abstract public function data(): array;

    abstract public function view(): string;

    public function title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title ?? 'Widget';
    }

    public function span(int $span): static
    {
        $this->span = $span;
        return $this;
    }

    public function getSpan(): int
    {
        return $this->span;
    }

    public function viewData(): array
    {
        return array_merge($this->data(), [
            'title' => $this->getTitle(),
            'span' => $this->span,
        ]);
    }
}
