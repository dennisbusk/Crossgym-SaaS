<div class="mx-auto max-w-3xl p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ __('View Role') }}</h1>
        <div class="space-x-2">
            <flux:button tag="a" href="{{ route('roles.index') }}" variant="primary">{{ __('Back') }}</flux:button>
            <flux:button tag="a" href="{{ route('roles.edit', $role) }}" variant="primary">{{ __('Edit') }}</flux:button>
        </div>
    </div>

    <div class="rounded border px-4 py-3 space-y-3">
        <div>
            <div class="text-sm text-gray-500">{{ __('Name') }}</div>
            <div class="font-medium">{{ $role->name }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500">{{ __('Users') }}</div>
            <div class="font-medium">{{ $role->users()->count() }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500">{{ __('Permissions') }}</div>
            @if (is_array($role->permissions) && count($role->permissions))
                <ul class="list-disc pl-5">
                    @foreach ($role->permissions as $key => $value)
                        <li><span class="font-medium">{{ $key }}</span>: <span class="text-gray-700">{{ is_array($value) ? json_encode($value) : (string) $value }}</span></li>
                    @endforeach
                </ul>
            @else
                <div class="text-gray-500">{{ __('No permissions set.') }}</div>
            @endif
        </div>
    </div>
</div>
