<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\UserSubscriptionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class UserForm extends Component
{
    use AuthorizesRequests;

    public ?User $user = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|max:255')]
    public string $email = '';

    // Leave blank to keep current password on edit
    #[Rule('nullable|string|min:8')]
    public string $password = '';

    #[Rule('required|integer|exists:roles,id')]
    public ?int $role_id = null;

    // Only visible to superadmin in the form
    #[Rule('nullable|integer|exists:tenants,id')]
    public ?int $tenant_id = null;

    // Optional plan selection to assign/swap
    #[Rule('nullable|integer|exists:plans,id')]
    public ?int $plan_id = null;

    public function mount(?User $user = null): void
    {
        $this->user = $user?->exists ? $user : null;

        if ($this->user) {
            $this->authorize('update', $this->user);
            $this->name = (string) $this->user->name;
            $this->email = (string) $this->user->email;
            $this->role_id = $this->user->role_id;
            $this->tenant_id = $this->user->tenant_id;
        } else {
            $this->authorize('create', User::class);
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Only include password if provided (on update)
        if ($this->password !== '') {
            $validated['password'] = $this->password; // Model cast will hash
        } else {
            unset($validated['password']);
        }

        if ($this->user) {
            $this->user->update($validated);
        } else {
            $this->user = User::create($validated);
        }

        // Handle plan assignment if selected
        if ($this->plan_id) {
            $plan = Plan::query()
                ->where('tenant_id', $this->tenant_id ?? tenant()?->id)
                ->find($this->plan_id);
            if ($plan) {
                $meta = (array) ($plan->metadata ?? []);
                $planType = (string) ($meta['plan_type'] ?? ($plan->interval === 'one_time' ? 'one_off' : 'subscription'));
                // Resolve via container to allow mocking in tests
                $service = app(UserSubscriptionService::class);
                if ($planType === 'subscription') {
                    $service->assignSubscriptionPlan($this->user, $plan);
                    session()->flash('status', __('Subscription assigned.'));
                } else {
                    $url = $service->assignOneOffPlan($this->user, $plan);
                    if ($url) {
                        session()->flash('status', __('One-off plan initiated. Complete payment to grant credits.'));
                        session()->flash('checkout_url', $url);
                    }
                }
            }
        }

        if (! $this->user->wasRecentlyCreated) {
            session()->flash('status', __('User updated.'));
        } else {
            session()->flash('status', __('User created.'));
        }

        $this->redirectRoute('users.index', navigate: true);
    }

    public function render()
    {
        // Current subscription details for display
        $currentSubscription = $this->user?->subscription;
        $currentPlan = null;
        if ($currentSubscription?->stripe_price_id) {
            $currentPlan = Plan::query()->where('stripe_price_id', $currentSubscription->stripe_price_id)->first();
        }

        return view('livewire.admin.users.form', [
            'roles' => Role::query()->visibleFor(Auth::user()->role->slug)->orderBy('name')->get(['id', 'name']),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'plans' => Plan::query()
                ->when(tenant(), fn ($q) => $q->where('tenant_id', tenant()->id))
                ->orderBy('name')
                ->get(['id', 'name', 'interval', 'metadata']),
            'currentSubscription' => $currentSubscription,
            'currentPlan' => $currentPlan,
        ]);
    }
}
