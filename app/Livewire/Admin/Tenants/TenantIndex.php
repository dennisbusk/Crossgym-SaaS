<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants;

use App\Exports\TenantsExport;
use App\Models\Tenant;
use App\Traits\WithSorting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class TenantIndex extends Component
{
    use AuthorizesRequests;
    use WithPagination;
    use WithSorting;

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

        $query = Tenant::query();
        $query = $this->applyFilters($query);

        return Excel::download(new TenantsExport($query), 'tenants.xlsx');
    }

    protected function applyFilters($query)
    {
        return $query;
    }

    public function render()
    {
        $tenants = Tenant::query();

        $tenants = $this->applyFilters($tenants);

        $tenants = $this->applySorting($tenants)->paginate(10);

        return view('livewire.admin.tenants.index', [
            'tenants' => $tenants,
        ]);
    }
}
