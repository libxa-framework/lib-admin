<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

/**
 * FileField — for file and image uploads.
 */
class FileField extends AdminField
{
    protected bool $isImage = false;
    protected array $acceptedTypes = [];

    public function image(): static
    {
        $this->isImage = true;
        $this->acceptedTypes = ['image/*'];
        return $this;
    }

    public function accept(array $types): static
    {
        $this->acceptedTypes = $types;
        return $this;
    }

    public function view(): string
    {
        return 'admin::fields.file';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'isImage' => $this->isImage,
            'acceptedTypes' => implode(',', $this->acceptedTypes),
        ]);
    }
}
