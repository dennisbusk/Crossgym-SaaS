<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Achievements;

use App\Models\Achievement;
use App\Traits\WithSorting;
use Livewire\Component;
use Livewire\WithPagination;

class AchievementIndex extends Component
{
    use WithPagination, WithSorting;

    public string $search = '';

    public function delete(Achievement $achievement): void
    {
        $this->authorize('delete', $achievement);
        $achievement->delete();
        $this->dispatch('banner-message', message: __('Achievement deleted successfully'), style: 'success');
    }

    public function render()
    {
        $achievements = Achievement::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.achievements.index', [
            'achievements' => $achievements,
        ]);
    }
}
