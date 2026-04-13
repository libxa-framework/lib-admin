<?php

declare(strict_types=1);

namespace Libxa\Admin\Columns;

abstract class AdminColumn
{
    protected string $name;
    protected ?string $label = null;
    protected bool $sortable = false;
    protected bool $searchable = false;
    protected bool $copyable = false;
    protected ?\Closure $formatter = null;
    protected bool $isHtml = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? ucfirst(str_replace('_', ' ', $this->name));
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;
        return $this;
    }

    public function isCopyable(): bool
    {
        return $this->copyable;
    }

    public function formatUsing(\Closure $formatter): static
    {
        $this->formatter = $formatter;
        return $this;
    }

    public function html(bool $allow = true): static
    {
        $this->isHtml = $allow;
        return $this;
    }

    public function dateTime(string $format = 'Y-m-d H:i:s'): static
    {
        return $this->formatUsing(fn($val) => $val ? date($format, is_numeric($val) ? (int)$val : strtotime($val)) : null);
    }

    public function view(): string
    {
        return 'admin.columns.text';
    }

    public function viewData(): array
    {
        return [
            'name'       => $this->name,
            'label'      => $this->getLabel(),
            'sortable'   => $this->sortable,
            'searchable' => $this->searchable,
            'copyable'   => $this->copyable,
            'isHtml'     => $this->isHtml,
            'formatter'  => $this->formatter,
        ];
    }

    /**
     * Serialize the column definition to an array.
     * Used by ResourceController to pass column metadata to views.
     */
    public function toArray(): array
    {
        return $this->viewData();
    }
}
