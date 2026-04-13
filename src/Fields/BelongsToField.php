<?php

declare(strict_types=1);

namespace Libxa\Admin\Fields;

/**
 * BelongsTo association field.
 */
class BelongsToField extends Select
{
    protected string $relation;
    protected string $displayColumn = 'name';

    /**
     * @param bool $searchable
     * @return $this
     */
    public function searchable(bool $searchable = true): static
    {
        return parent::searchable($searchable);
    }

    public function __construct(string $name, string $relation = '')
    {
        parent::__construct($name);
        $this->relation = $relation ?: $name;
    }

    public function displayUsing(string $column): static
    {
        $this->displayColumn = $column;
        return $this;
    }

    public function view(): string
    {
        return 'admin::fields.belongs-to';
    }

    public function viewData(): array
    {
        // In a real implementation, we would fetch options from the related model.
        // For this "Full Project", we'll simulate the options or expect the controller to populate them.
        return array_merge(parent::viewData(), [
            'relation' => $this->relation,
            'displayColumn' => $this->displayColumn,
        ]);
    }
}
