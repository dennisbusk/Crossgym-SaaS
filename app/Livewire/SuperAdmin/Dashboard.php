<?php

declare(strict_types=1);

namespace App\Livewire\SuperAdmin;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionOption;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    public int $tenantsCount = 0;
    public int $usersCount = 0;
    public int $plansCount = 0;
    public int $subscriptionsCount = 0;

    /**
     * Subscription overview rows: [name, tenants, earned_dkk|null, type]
     * @var array<int, array<string, mixed>>
     */
    public array $subscriptionOverview = [];

    public function mount(): void
    {
        $this->tenantsCount = Tenant::count();
        $this->usersCount = User::count();
        $this->plansCount = class_exists(Plan::class) ? Plan::count() : 0;
        $this->subscriptionsCount = class_exists(Subscription::class) ? Subscription::count() : 0;

        // Load subscription options with tenant counts
        if (class_exists(SubscriptionOption::class)) {
            $this->subscriptionOverview = SubscriptionOption::query()
                ->withCount('tenants')
                ->get()
                ->map(function (SubscriptionOption $option): array {
                    return [
                        'name' => $option->name,
                        'tenants' => $option->tenants_count,
                        // Earnings calculation depends on available business data; placeholder null
                        'earned_dkk' => null,
                        'type' => $option->type,
                    ];
                })
                ->toArray();
        }
    }

    public function render(): View
    {
        return view('livewire.superadmin.dashboard');
    }
}
