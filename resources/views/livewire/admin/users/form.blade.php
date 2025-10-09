<div class="space-y-6 max-w-2xl">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">{{ $user?->exists ? __('Edit User') : __('Create User') }}</h1>
    <flux:button tag="a" href="{{ route('users.index') }}" icon="arrow-left" variant="ghost">{{ __('Back') }}</flux:button>
  </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif
  
  <form wire:submit.prevent="save" class="space-y-5">
    <div class="grid grid-cols-1 gap-5">
      <div>
        <label class="block text-sm font-medium">{{ __('Name') }}</label>
        <flux:input wire:model.defer="name"/>
        @error('name')
        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
      </div>
      
      <div>
        <label class="block text-sm font-medium">{{ __('Email') }}</label>
        <flux:input type="email" wire:model.defer="email"/>
        @error('email')
        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
      </div>
      
      <div>
        <label class="block text-sm font-medium">{{ __('Role') }}</label>
        <flux:select wire:model.defer="role_id">
          <option value="">{{ __('Select role') }}</option>
          @foreach($roles as $role)
            <option value="{{ $role->id }}">{{ $role->name }}</option>
          @endforeach
        </flux:select>
        @error('role_id')
        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
      </div>
      
      <div>
        <label class="block text-sm font-medium">{{ __('Tenant') }}</label>
        <flux:select wire:model.defer="tenant_id">
          <option value="">{{ __('Select tenant') }}</option>
          @foreach($tenants as $tenant)
            <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
          @endforeach
        </flux:select>
        @error('tenant_id')
        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
      </div>
      
      <div>
        <label class="block text-sm font-medium">{{ __('Password') }}</label>
        <flux:input type="password" wire:model.defer="password"/>
        @error('password')
        <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
        @enderror
        @if($user)
          <p class="text-xs text-gray-500 mt-1">{{ __('Leave blank to keep current password') }}</p>
        @endif
      </div>
        </div>
    
    <div class="flex items-center justify-end gap-2 pt-4">
      <flux:button type="submit" variant="primary">
                {{ $user?->exists ? __('Update') : __('Create') }}
      </flux:button>
      <flux:button tag="a" href="{{ route('users.index') }}" variant="secondary">{{ __('Cancel') }}</flux:button>
        </div>
    </form>
</div>
