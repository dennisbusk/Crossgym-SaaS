<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Password;
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

    public function impersonate()
    {
        $this->authorize('impersonate', $this->user);

        if (auth()->user()->canImpersonate() && $this->user->canBeImpersonated()) {
            auth()->user()->impersonate($this->user);

            return redirect()->route('dashboard');
        }

        return null;
    }

    public function sendResetPassword(): void
    {
        $this->authorize('update', $this->user);

        Password::broker()->sendResetLink(['email' => $this->user->email]);

        $this->dispatch('notify', [
            'message' => __('A reset email has been sent to the user.'),
            'variant' => 'success',
        ]);
    }

    public function render()
    {
        $pastBookings = $this->user->attendingClasses()
            ->where('class_start', '<', now())
            ->orderBy('class_start', 'desc')
            ->get();

        $upcomingBookings = $this->user->attendingClasses()
            ->where('class_start', '>=', now())
            ->orderBy('class_start', 'asc')
            ->get();

        return view('livewire.admin.users.show', [
            'user' => $this->user->load(['role', 'tenant']),
            'pastBookings' => $pastBookings,
            'upcomingBookings' => $upcomingBookings,
        ]);
    }
}
