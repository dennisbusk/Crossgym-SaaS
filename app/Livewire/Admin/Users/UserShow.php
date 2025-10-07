<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class UserShow extends Component
{
    use AuthorizesRequests;

    public User $user;

    public function mount(User $user): void
    {
        $this->authorize('view', $user);
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.admin.users.show', [
            'user' => $this->user->load(['role', 'tenant']),
        ]);
    }
}
