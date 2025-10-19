<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TenantForm extends Component
{
    use AuthorizesRequests;

    public ?Tenant $tenant = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public string $domain = '';

    public function mount($tenant = null): void
    {
        $this->tenant = $tenant instanceof Tenant ? $tenant : new Tenant();

        if ($tenant) {
            $this->authorize('update', $tenant);
            $this->name = $tenant->name;
            $this->domain = $tenant->domain;
        } else {
            $this->authorize('create', Tenant::class);
        }
    }


    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'domain' => [
                'required', 'string', 'max:255',
                Rule::unique('tenants', 'domain')->ignore($this->tenant?->id),
            ],
        ];

        $validated = $this->validate($rules);

        if ($this->tenant) {
            $this->tenant->update($validated);
            session()->flash('status', 'Tenant updated.');
        } else {
            $this->tenant = Tenant::create($validated);
            session()->flash('status', 'Tenant created.');
        }

        $this->redirectRoute('tenants.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.tenants.form');
    }
}
