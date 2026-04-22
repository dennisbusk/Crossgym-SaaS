<?php

namespace App\Livewire\Components;

use App\Models\Tenant;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class TenantSwitcher extends Component
{
    public $selectedTenant;

    public function mount()
    {
        $this->selectedTenant = tenant()->id;
    }

    public function switchTenant()
    {
        $tenant = Tenant::find($this->selectedTenant);
        if (! $tenant) {
            return;
        }
        $key = Crypt::encrypt(now()->addSeconds(30)->timestamp);
        $signedUrl = URL::route(
            'superadmin.switch-tenant',
            [
                'superadmin_id' => auth()->id(),
                'email' => auth()->user()->email,
                'tenant_id' => $tenant->id,
                'crypt_key' => $key,
            ]
        );
        $signedUrl = str_replace(tenant()->domain, $tenant->domain, $signedUrl);

        return redirect()->away($signedUrl);
    }

    public function render()
    {
        return view('livewire.components.tenant-switcher')->with(['tenants' => Tenant::all()]);
    }
}
