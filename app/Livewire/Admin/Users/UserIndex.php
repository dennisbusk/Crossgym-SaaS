<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Exports\UsersExport;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use App\Traits\WithSorting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class UserIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithSorting;

    public $search = '';

    public $roleFilter = '';

    public $planFilter = '';

    public $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPlanFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function checkIn(User $user): void
    {
        $this->authorize('update', $user);

        $now = now();
        \App\Models\CheckIn::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'checked_at' => $now,
        ]);

        session()->flash('status', __('Check-in registered for :name', ['name' => $user->name]));
    }

    public function delete(User $user): void
    {
        $this->authorize('delete', $user);
        $user->delete();
        session()->flash('status', __('User deleted.'));
    }

    public function export()
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->with(['subscription', 'subscription.plan', 'role']);
        $query = $this->applyFilters($query);

        return Excel::download(new UsersExport($query), 'users.xlsx');
    }

    protected function applyFilters($query)
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->roleFilter) {
            $query->where('role_id', $this->roleFilter);
        }

        if ($this->planFilter) {
            $query->whereHas('subscription', function ($query) {
                $query->where('stripe_price_id', $this->planFilter);
            });
        }

        if ($this->statusFilter) {
            $query->whereHas('subscription', function ($query) {
                $query->where('status', $this->statusFilter);
            });
        }

        if (auth()->user()->role?->slug != 'superadmin') {
            $query->whereHas('role', function ($query) {
                $query->where('slug', '!=', 'superadmin');
            });
        }

        return $query;
    }

    public function render()
    {
        $users = User::query()->with(['subscription', 'subscription.plan', 'role'])
            ->withMax('checkIns as last_check_in_at', 'checked_at');

        $users = $this->applyFilters($users);

        $users = $this->applySorting($users)->paginate(10);

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => Role::withGlobalRoles()->get(),
            'plans' => Plan::all(),
            'statuses' => Subscription::select('status')->distinct()->whereNotNull('status')->pluck('status'),
        ]);
    }
}
