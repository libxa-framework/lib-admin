<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

class TextInput extends AdminField
{
    protected int $maxLength = 255;
    protected string $placeholder = '';
    protected bool $autoComplete = true;
    /** Input type: text | email | password | number | url | tel | date | time | color | range */
    protected string $inputType = 'text';

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

    /** Make the input render as <input type="email"> */
    public function email(): static
    {
        $this->inputType = 'email';
        return $this;
    }

    /** Make the input render as <input type="password"> */
    public function password(): static
    {
        $this->inputType = 'password';
        $this->autoComplete = false;
        return $this;
    }

    /** Make the input render as <input type="number"> */
    public function numeric(): static
    {
        $this->inputType = 'number';
        return $this;
    }

    /** Make the input render as <input type="url"> */
    public function url(): static
    {
        $this->inputType = 'url';
        return $this;
    }

    /** Make the input render as <input type="tel"> */
    public function tel(): static
    {
        $this->inputType = 'tel';
        return $this;
    }

    /** Make the input render as <input type="date"> */
    public function date(): static
    {
        $this->inputType = 'date';
        return $this;
    }

    /** Set an arbitrary HTML input type */
    public function type(string $type): static
    {
        $this->inputType = $type;
        return $this;
    }

    /** Blade view hint for this field (registered under the "admin" namespace) */
    public function view(): string
    {
        return 'admin::fields.text';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'type'        => 'text',          // the partial type key
            'inputType'   => $this->inputType, // the actual HTML input type
            'maxLength'   => $this->maxLength,
            'placeholder' => $this->placeholder,
            'autoComplete'=> $this->autoComplete,
        ]);
    }

    public function rules(): array
    {
        $rules = parent::rules();

        if ($this->inputType === 'email') {
            $rules[] = 'email';
        } elseif ($this->inputType === 'url') {
            $rules[] = 'url';
        } elseif ($this->inputType === 'number') {
            $rules[] = 'numeric';
        } else {
            $rules[] = 'string';
        }

        $rules[] = 'max:' . $this->maxLength;

        return $rules;
    }
}
