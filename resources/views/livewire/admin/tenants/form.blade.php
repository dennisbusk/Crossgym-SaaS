<div class="space-y-6 max-w-xl">
    <h1 class="text-2xl font-semibold">{{ $tenant?->exists ? 'Edit Tenant' : 'Create Tenant' }}</h1>

    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ session('status') }}</div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input type="text" wire:model.defer="name" class="mt-1 w-full rounded-md border px-3 py-2" />
            @error('name')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Domain</label>
            <input type="text" wire:model.defer="domain" class="mt-1 w-full rounded-md border px-3 py-2" placeholder="e.g. gym1.localhost" />
            @error('domain')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                {{ $tenant?->exists ? 'Update' : 'Create' }}
            </button>
            <a href="{{ route('tenants.index') }}" class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 hover:bg-gray-200">Cancel</a>
        </div>
    </form>
</div>
