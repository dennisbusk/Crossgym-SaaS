<div class="space-y-6 max-w-2xl">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">{{ __('User Details') }}</h1>
    <div class="flex gap-2">
      <flux:button tag="a" href="{{ route('users.index') }}" icon="arrow-left" variant="ghost">{{ __('Back to list') }}</flux:button>
      <flux:button tag="a" href="{{ route('users.edit', $user) }}" icon="pencil-square" variant="primary">{{ __('Edit') }}</flux:button>
    </div>
  </div>
  
  <div class="rounded-2xl border p-4 space-y-2 bg-white/5">
        <div><span class="font-medium">{{ __('Name') }}:</span> {{ $user->name }}</div>
        <div><span class="font-medium">{{ __('Email') }}:</span> {{ $user->email }}</div>
        <div><span class="font-medium">{{ __('Role') }}:</span> {{ $user->role?->name ?? __('N/A') }}</div>
        <div><span class="font-medium">{{ __('Tenant') }}:</span> {{ $user->tenant?->name ?? __('N/A') }}</div>
        <div><span class="font-medium">{{ __('Created At') }}:</span> {{ $user->created_at }}</div>
    </div>
</div>
