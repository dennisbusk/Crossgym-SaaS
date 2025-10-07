<div class="mx-auto max-w-3xl p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ __('View Tenant') }}</h1>
        <div class="space-x-2">
            <flux:button tag="a" href="{{ route('tenants.index') }}" variant="primary">{{ __('Back') }}</flux:button>
            <flux:button tag="a" href="{{ route('tenants.edit', $tenant) }}" variant="primary">{{ __('Edit') }}</flux:button>
        </div>
    </div>

    <div class="rounded border px-4 py-3 space-y-3">
        <div>
            <div class="text-sm text-gray-500">{{ __('ID') }}</div>
            <div class="font-medium">{{ $tenant->id }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500">{{ __('Name') }}</div>
            <div class="font-medium">{{ $tenant->name }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500">{{ __('Domain') }}</div>
            <div class="font-medium">{{ $tenant->domain }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500">{{ __('Created') }}</div>
            <div class="font-medium">{{ optional($tenant->created_at)->format('Y-m-d H:i') }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500">{{ __('Updated') }}</div>
            <div class="font-medium">{{ optional($tenant->updated_at)->format('Y-m-d H:i') }}</div>
        </div>
    </div>
</div>
