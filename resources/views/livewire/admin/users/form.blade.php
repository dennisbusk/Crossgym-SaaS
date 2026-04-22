<section class="w-full max-w-2xl">
    <h1 class="text-2xl font-semibold mb-4">
        {{ $user?->exists ? __('Edit User') : __('Create User') }}
    </h1>

    <form wire:submit="save" class="space-y-4">
        <flux:input wire:model="name" :label="__('Name')" required />
        <flux:input wire:model="email" :label="__('Email')" type="email" required />
        <flux:input wire:model="password" :label="__('Password')" type="password" />

        <flux:select wire:model="role_id" :label="__('Role')">
            <option value="">-- {{ __('Select role') }} --</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model="tenant_id" :label="__('Tenant')">
            <option value="">-- {{ __('Select tenant') }} --</option>
            @foreach($tenants as $tenant)
                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model="plan_id" :label="__('Assign plan (optional)')">
            <option value="">-- {{ __('None') }} --</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
            @endforeach
        </flux:select>

        <div class="flex justify-end gap-2 pt-4">
            <flux:link href="{{ route('users.index') }}" variant="ghost">{{ __('Cancel') }}</flux:link>
            <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
        </div>
    </form>
</section>
