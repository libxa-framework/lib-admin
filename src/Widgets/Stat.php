<?php

declare(strict_types=1);

namespace Libxa\Admin\Widgets;

class Stat
{
    protected string $label;
    protected string $value;
    protected string $description = '';
    protected string $descriptionIcon = '';
    protected string $descriptionColor = 'slate';
    protected string $icon = '';
    protected string $color = 'primary'; // Used for the main icon background
    protected array $chart = [];
    protected string $chartColor = 'primary';

    public function __construct(string $label, string $value)
    {
        $this->label = $label;
        $this->value = $value;
    }

    public static function make(string $label, string $value): self
    {
        return new self($label, $value);
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function descriptionIcon(string $icon): self
    {
        $this->descriptionIcon = $icon;
        return $this;
    }

    public function descriptionColor(string $color): self
    {
        $this->descriptionColor = $color;
        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function chart(array $data): self
    {
        $this->chart = $data;
        return $this;
    }

    public function chartColor(string $color): self
    {
        $this->chartColor = $color;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'label'            => $this->label,
            'value'            => $this->value,
            'description'      => $this->description,
            'descriptionIcon'  => $this->descriptionIcon,
            'descriptionColor' => $this->descriptionColor,
            'icon'             => $this->icon,
            'color'            => $this->color,
            'chart'            => $this->chart,
            'chartColor'       => $this->chartColor,
        ];
    }
}
