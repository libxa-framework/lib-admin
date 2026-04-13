<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

/**
 * A <textarea> field for admin resource forms.
 *
 * Usage:
 *   TextareaField::make('description')
 *       ->rows(5)
 *       ->placeholder('Enter a short description…')
 *       ->required();
 */
class Textarea extends AdminField
{
    protected int    $rows        = 4;
    protected string $placeholder = '';

    public function rows(int $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function view(): string
    {
        return 'admin::fields.textarea';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'type'        => 'textarea',
            'rows'        => $this->rows,
            'placeholder' => $this->placeholder,
        ]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), ['string']);
    }
}
