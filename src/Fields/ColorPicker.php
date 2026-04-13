<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

/**
 * A color picker field for hex values.
 */
class ColorPicker extends AdminField
{
    public function view(): string
    {
        return 'admin::fields.color-picker';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'type' => 'color',
        ]);
    }
}
