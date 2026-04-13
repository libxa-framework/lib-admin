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
    
    // Validation properties
    protected ?string $uniqueTable = null;
    protected ?string $uniqueColumn = null;
    protected mixed $uniqueIgnore = null;
    protected array $rules = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Create a new field instance.
     * Variadic to allow sub-classes to have custom constructors.
     */
    public static function make(mixed ...$args): static
    {
        return new static(...$args);
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

    public function helperText(string $text): static
    {
        return $this->hint($text);
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

    public function unique(string $table, ?string $column = null, mixed $ignore = null): static
    {
        $this->uniqueTable = $table;
        $this->uniqueColumn = $column;
        $this->uniqueIgnore = $ignore;
        return $this;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    public function rules(): array
    {
        $rules = $this->rules;

        if ($this->required) {
            $rules[] = 'required';
        } elseif ($this->nullable) {
            $rules[] = 'nullable';
        }

        if ($this->uniqueTable) {
            $unique = "unique:{$this->uniqueTable}";
            if ($this->uniqueColumn) {
                $unique .= ",{$this->uniqueColumn}";
            }
            if ($this->uniqueIgnore) {
                $unique .= ",{$this->uniqueIgnore}";
            }
            $rules[] = $unique;
        }

        return $rules;
    }
}
