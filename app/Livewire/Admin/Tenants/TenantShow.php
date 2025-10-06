<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class TenantShow extends Component
{
    use AuthorizesRequests;

    public Tenant $tenant;

    public function mount(Tenant $tenant): void
    {
        $this->authorize('view', $tenant);
        $this->tenant = $tenant;
    }

    public function render()
    {
        return view('livewire.admin.tenants.show', [
            'tenant' => $this->tenant,
        ]);
    }
}
