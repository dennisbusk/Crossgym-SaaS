<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants;

use App\Exports\TenantsExport;
use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

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
        session()->flash('status', __('Tenant deleted.'));
    }

    public function export()
    {
        $this->authorize('viewAny', Tenant::class);

        return Excel::download(new TenantsExport(), 'tenants.xlsx');
    }

    public function render()
    {
        return view('livewire.admin.tenants.index', [
            'tenants' => Tenant::query()->latest()->get(),
        ]);
    }
}
