<div class="space-y-6 max-w-xl">
    <h1 class="text-2xl font-semibold">{{ __('User Details') }}</h1>

    <div class="rounded-md border p-4 space-y-2">
        <div><span class="font-medium">{{ __('Name') }}:</span> {{ $user->name }}</div>
        <div><span class="font-medium">{{ __('Email') }}:</span> {{ $user->email }}</div>
        <div><span class="font-medium">{{ __('Role') }}:</span> {{ $user->role?->name ?? __('N/A') }}</div>
        <div><span class="font-medium">{{ __('Tenant') }}:</span> {{ $user->tenant?->name ?? __('N/A') }}</div>
        <div><span class="font-medium">{{ __('Created At') }}:</span> {{ $user->created_at }}</div>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">{{ __('Edit') }}</a>
        <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 hover:bg-gray-200">{{ __('Back to list') }}</a>
    </div>
</div>
