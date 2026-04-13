<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

/**
 * MultiSelect field for many-to-many relationships.
 */
class MultiSelect extends Select
{
    protected string $relationship;
    protected string $displayColumn = 'name';

    public function __construct(string $name, string $relationship = '')
    {
        parent::__construct($name);
        $this->relationship = $relationship ?: $name;
    }

    public function relationship(string $relationship, string $displayColumn = 'name'): static
    {
        $this->relationship  = $relationship;
        $this->displayColumn = $displayColumn;
        return $this;
    }

    public function view(): string
    {
        return 'admin::fields.multi-select';
    }

    public function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'relationship'  => $this->relationship,
            'displayColumn' => $this->displayColumn,
            'multiple'      => true,
        ]);
    }
}
