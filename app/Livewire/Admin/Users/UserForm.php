<?php

namespace App\Livewire\Admin\Users;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserForm extends Component
{
    use AuthorizesRequests;

    public ?User $user = null;

    public string $name = '';
    public string $email = '';
    public ?int $role_id = null;
    public ?int $tenant_id = null;
    public string $password = '';

    public function mount($user = null): void
    {
        $this->user = $user && $user->exists ? $user : null;

        if ($this->user) {
            $this->authorize('update', $this->user);
            $this->name = (string) $this->user->name;
            $this->email = (string) $this->user->email;
            $this->role_id = $this->user->role_id;
            $this->tenant_id = $this->user->tenant_id;
        } else {
            $this->authorize('create', User::class);
            $this->tenant_id = tenant()->id ?? Auth::user()->tenant_id;
        }
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->user?->id),
            ],
            'role_id' => ['nullable', 'exists:roles,id'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ];

        if (!$this->user) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } elseif ($this->password !== '') {
            $rules['password'] = ['string', 'min:8'];
        }

        $validated = $this->validate($rules);

        // Only include password if provided (on update)
        if ($this->password !== '') {
            $validated['password'] = $this->password; // Model cast will hash
        } else {
            unset($validated['password']);
        }

        if ($this->user) {
            $this->user->update($validated);
            session()->flash('status', __('User updated.'));
        } else {
            $this->user = User::create($validated);
            session()->flash('status', __('User created.'));
        }

        $this->redirectRoute('users.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.users.form', [
            'roles' => Role::query()->visibleFor(Auth::user()->role->slug)->orderBy('name')->get(['id', 'name']),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
