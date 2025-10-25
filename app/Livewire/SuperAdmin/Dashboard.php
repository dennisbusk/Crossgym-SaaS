<?php

declare(strict_types=1);

namespace App\Livewire\SuperAdmin;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    public int $tenantsCount = 0;
    public int $usersCount = 0;
    public int $plansCount = 0;
    public int $subscriptionsCount = 0;

    public function mount(): void
    {
        $this->tenantsCount = Tenant::count();
        $this->usersCount = User::count();
        $this->plansCount = class_exists(Plan::class) ? Plan::count() : 0;
        $this->subscriptionsCount = class_exists(Subscription::class) ? Subscription::count() : 0;
    }

    public function render(): View
    {
        return view('livewire.superadmin.dashboard');
    }
}
