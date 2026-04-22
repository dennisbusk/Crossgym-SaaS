<?php

declare(strict_types=1);

namespace App\Traits;

use Livewire\Attributes\Url;

trait WithSorting
{
    #[Url]
    public string $sortField = 'id';

    #[Url]
    public string $sortDirection = 'desc';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function applySorting($query)
    {
        return $query->orderBy($this->sortField, $this->sortDirection);
    }
}
