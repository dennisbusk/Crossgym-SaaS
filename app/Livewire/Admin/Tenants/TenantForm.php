<?php

namespace App\Livewire\Admin\Tenants;

use App\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class TenantForm extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public ?Tenant $tenant = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public string $domain = '';

    public string $app_name = '';

    public $icon;

    public string $theme_color = '#000000';

    public string $background_color = '#ffffff';

    public string $terms = '';

    public bool $allow_member_billing_management = true;

    public function mount($tenant = null): void
    {
        $this->tenant = $tenant instanceof Tenant ? $tenant : null;

        if ($this->tenant) {
            $this->authorize('update', $this->tenant);
            $this->name = $this->tenant->name;
            $this->domain = $this->tenant->domain;
            $this->app_name = $this->tenant->app_name ?? '';
            $this->theme_color = $this->tenant->theme_color ?? '#000000';
            $this->background_color = $this->tenant->background_color ?? '#ffffff';
            $this->terms = (string) ($this->tenant->getTranslation('terms', app()->getLocale()) ?? '');
            $this->allow_member_billing_management = (bool) ($this->tenant->allow_member_billing_management ?? true);
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
            'app_name' => ['nullable', 'string', 'max:255'],
            'theme_color' => ['nullable', 'string', 'max:7'],
            'background_color' => ['nullable', 'string', 'max:7'],
            'icon' => ['nullable', 'image', 'max:1024'],
            'terms' => ['nullable', 'string'],
            'allow_member_billing_management' => ['boolean'],
        ];

        $validated = $this->validate($rules);

        if ($this->icon) {
            $validated['icon_path'] = $this->icon->store('icons', 'public');
        }

        unset($validated['icon']);

        $data = $validated;
        $data['terms'] = [app()->getLocale() => $this->terms];

        if ($this->tenant) {
            $this->tenant->update($data);
            session()->flash('status', __('Tenant updated.'));
        } else {
            $this->tenant = Tenant::create($data);
            session()->flash('status', __('Tenant created.'));
        }

        if (Auth::user()->can('viewAny', Tenant::class)) {
            $this->redirectRoute('tenants.index', navigate: true);
        } else {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.admin.tenants.form');
    }
}
