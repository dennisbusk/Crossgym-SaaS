<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Calendar extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.calendar');
    }
}
