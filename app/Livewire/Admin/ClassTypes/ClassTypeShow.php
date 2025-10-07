<?php

declare(strict_types=1);

namespace App\Livewire\Admin\ClassTypes;

use App\Models\ClassType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ClassTypeShow extends Component
{
    use AuthorizesRequests;

    public ClassType $classType;

    public function mount(ClassType $classType): void
    {
        $this->classType = $classType;
        $this->authorize('view', $this->classType);
    }

    public function render()
    {
        return view('livewire.admin.class-types.show');
    }
}
