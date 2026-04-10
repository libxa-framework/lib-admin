<?php

declare(strict_types=1);

namespace Libxa\Admin\Actions;

abstract class Action
{
    protected string $name;
    protected string $label;
    protected string $icon = 'play';
    protected string $color = 'primary';
    protected bool $requiresConfirmation = false;
    protected string $confirmationMessage = '';

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->label = ucfirst($name);
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
        return $this->label;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function requiresConfirmation(bool $requires = true, string $message = ''): static
    {
        $this->requiresConfirmation = $requires;
        $this->confirmationMessage = $message;
        return $this;
    }

    public function getConfirmationMessage(): string
    {
        return $this->confirmationMessage ?: "Are you sure you want to perform this action?";
    }

    abstract public function handle($record, array $data = []): void;
}
