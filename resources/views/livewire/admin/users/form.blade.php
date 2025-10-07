<div class="space-y-6 max-w-xl">
    <h1 class="text-2xl font-semibold">{{ $user?->exists ? __('Edit User') : __('Create User') }}</h1>

    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">{{ __('Name') }}</label>
            <input type="text" wire:model.defer="name" class="mt-1 w-full rounded-md border px-3 py-2" />
            @error('name')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('Email') }}</label>
            <input type="email" wire:model.defer="email" class="mt-1 w-full rounded-md border px-3 py-2" />
            @error('email')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('Role') }}</label>
            <select wire:model.defer="role_id" class="mt-1 w-full rounded-md border px-3 py-2">
                <option value="">{{ __('Select role') }}</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('Tenant') }}</label>
            <select wire:model.defer="tenant_id" class="mt-1 w-full rounded-md border px-3 py-2">
                <option value="">{{ __('Select tenant') }}</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </select>
            @error('tenant_id')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('Password') }}</label>
            <input type="password" wire:model.defer="password" class="mt-1 w-full rounded-md border px-3 py-2" />
            @error('password')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
            @if($user)
                <p class="text-xs text-gray-500 mt-1">{{ __('Leave blank to keep current password') }}</p>
            @endif
        </div>

        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                {{ $user?->exists ? __('Update') : __('Create') }}
            </button>
            <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 hover:bg-gray-200">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
