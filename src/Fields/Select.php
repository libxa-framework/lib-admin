<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

/**
 * A <select> / dropdown field for admin resource forms.
 *
 * Usage:
 *   SelectField::make('role')
 *       ->options(['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer'])
 *       ->required()
 *       ->label('User Role');
 */
class Select extends AdminField
{
    /** @var array<string|int, string> Key→label option pairs */
    protected array $options     = [];
    protected string $icon       = 'expand_more';
    protected bool $searchable   = false;

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function view(): string
    {
        return 'admin::fields.select';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'type'       => 'select',
            'options'    => $this->options,
            'icon'       => $this->icon,
            'searchable' => $this->searchable,
        ]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'in:' . implode(',', array_keys($this->options)),
        ]);
    }
}
