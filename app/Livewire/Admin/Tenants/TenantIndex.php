<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TenantIndex extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('viewAny', Tenant::class);
    }

    public function delete(Tenant $tenant): void
    {
        $this->authorize('delete', $tenant);
        $tenant->delete();
        session()->flash('status', 'Tenant deleted.');
    }

    public function render()
    {
        return view('livewire.admin.tenants.index', [
            'tenants' => Tenant::query()->latest()->get(),
        ]);
    }
}
