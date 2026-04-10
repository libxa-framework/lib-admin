<?php

declare(strict_types=1);

namespace Libxa\Admin\Filters;

abstract class Filter
{
    protected string $name;
    protected ?string $label = null;

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

    abstract public function apply($query, $value): void;
}
