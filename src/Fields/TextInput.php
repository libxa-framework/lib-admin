<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

class TextInput extends AdminField
{
    protected int $maxLength = 255;
    protected string $placeholder = '';
    protected bool $autoComplete = true;

    public function maxLength(int $maxLength): static
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function autoComplete(bool $autoComplete = true): static
    {
        $this->autoComplete = $autoComplete;
        return $this;
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'maxLength' => $this->maxLength,
            'placeholder' => $this->placeholder,
            'autoComplete' => $this->autoComplete,
        ]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'string',
            'max:' . $this->maxLength,
        ]);
    }
}
