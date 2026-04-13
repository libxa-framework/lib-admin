<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

/**
 * A boolean toggle (checkbox rendered as a sliding switch) for admin resource forms.
 *
 * Usage:
 *   ToggleField::make('is_active')
 *       ->label('Active Status')
 *       ->hint('Enable or disable this account.')
 *       ->icon('toggle_on')
 *       ->default(true);
 */
class Toggle extends AdminField
{
    protected string $icon = 'toggle_on';

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function view(): string
    {
        return 'admin::fields.toggle';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'type' => 'toggle',
            'icon' => $this->icon,
        ]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), ['boolean']);
    }
}
