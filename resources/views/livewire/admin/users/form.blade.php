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
            <input type="email" wire:model.defer="email" class="mt-1 w-full rounded-md border px-3 py-2" autocomplete="new-email" />
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
@if(hasRole('superadmin'))
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
        @endif
        <div x-data="{
        password: @entangle('password'),
        generateNewPassword()
        {
          const lowercase = 'abcdefghijklmnopqrstuvwxyz';
          const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
          const numbers = '0123456789';
          const length = 12; // Using 12 characters for better security

          let password = '';

          // Ensure at least one of each required character type
          password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
          password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
          password += numbers.charAt(Math.floor(Math.random() * numbers.length));

          // Fill the rest with random characters from all sets
          const allChars = lowercase + uppercase + numbers;
          for (let i = password.length; i < length; i++) {
            password += allChars.charAt(Math.floor(Math.random() * allChars.length));
          }

          // Shuffle the password to make it more random
          password = password.split('').sort(() => Math.random() - 0.5).join('');

          // Set the new password
          this.password = password;
        }
        }
        ">
            <label class="block text-sm font-medium">{{ __('Password') }}</label>
{{--            <input type="password" wire:model.defer="password" class="mt-1 w-full rounded-md border px-3 py-2" autocomplete="new-password" />--}}
            <div class="relative flex">
                <input type="text" class="mt-2 w-full rounded-full border-none" x-model="password"
                       @input.debounce.500ms="validatePassword()"
                       autocomplete="new-password">
                <x-flowbite.button.yellow class="absolute right-0 mr-0 border-none mt-1 top-1/2 border-2 font-semibold rounded-r-full px-2 py-1 -translate-y-1/2" @click="generateNewPassword()">
                    {{__('generate new password')}}
                </x-flowbite.button.yellow>
            </div>
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
            <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 hover:bg-yellow-800">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
