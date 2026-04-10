<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

abstract class AdminField
{
    protected string $name;
    protected ?string $label = null;
    protected bool $required = false;
    protected bool $nullable = true;
    protected mixed $default = null;
    protected string $hint = '';
    protected bool $hiddenOnCreate = false;
    protected bool $hiddenOnEdit = false;
    protected bool $hiddenOnView = false;
    protected bool $onlyOnCreate = false;
    protected bool $onlyOnEdit = false;
    protected bool $onlyOnView = false;

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

    public function required(bool $required = true): static
    {
        $this->required = $required;
        $this->nullable = ! $required;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;
        $this->required = ! $nullable;
        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;
        return $this;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function hint(string $hint): static
    {
        $this->hint = $hint;
        return $this;
    }

    public function getHint(): string
    {
        return $this->hint;
    }

    public function hiddenOnCreate(bool $hidden = true): static
    {
        $this->hiddenOnCreate = $hidden;
        return $this;
    }

    public function hiddenOnEdit(bool $hidden = true): static
    {
        $this->hiddenOnEdit = $hidden;
        return $this;
    }

    public function hiddenOnView(bool $hidden = true): static
    {
        $this->hiddenOnView = $hidden;
        return $this;
    }

    public function onlyOnCreate(bool $only = true): static
    {
        $this->onlyOnCreate = $only;
        return $this;
    }

    public function onlyOnEdit(bool $only = true): static
    {
        $this->onlyOnEdit = $only;
        return $this;
    }

    public function onlyOnView(bool $only = true): static
    {
        $this->onlyOnView = $only;
        return $this;
    }

    public function view(): string
    {
        return 'admin.fields.text';
    }

    public function viewData(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->getLabel(),
            'required' => $this->required,
            'nullable' => $this->nullable,
            'default' => $this->default,
            'hint' => $this->hint,
            'hiddenOnCreate' => $this->hiddenOnCreate,
            'hiddenOnEdit' => $this->hiddenOnEdit,
            'hiddenOnView' => $this->hiddenOnView,
            'onlyOnCreate' => $this->onlyOnCreate,
            'onlyOnEdit' => $this->onlyOnEdit,
            'onlyOnView' => $this->onlyOnView,
        ];
    }

    public function rules(): array
    {
        $rules = [];

        if ($this->required) {
            $rules[] = 'required';
        } elseif ($this->nullable) {
            $rules[] = 'nullable';
        }

        return $rules;
    }
}
