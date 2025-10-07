<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Classes;

use App\Models\GymClass;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ClassShow extends Component
{
    use AuthorizesRequests;

    public GymClass $gymClass;

    public function mount(GymClass $gymClass): void
    {
        // Eager load related data including participants for display
        $this->gymClass = $gymClass->load(['trainer', 'classType', 'participants']);
        $this->authorize('view', $this->gymClass);
    }

    public function render()
    {
        return view('livewire.admin.classes.show');
    }
}
