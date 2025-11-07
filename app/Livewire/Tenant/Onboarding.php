<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Onboarding extends Component
{
    public int $step = 1;

    #[Layout('components.layouts.app')]
    public function render(): View
    {
        return view('livewire.tenant.onboarding');
    }

    public function next(): void
    {
        $this->step = min(4, $this->step + 1);
    }

    public function prev(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function complete(): \Symfony\Component\HttpFoundation\Response
    {
        $user = Auth::user();
        $tenant = method_exists($user, 'tenant') ? $user->tenant : null;

        if ($tenant) {
            $tenant->onboarded_at = now();
            $tenant->save();
        }

        session()->flash('success', __('You\'re all set!'));

        return redirect()->route('dashboard');
    }
}
