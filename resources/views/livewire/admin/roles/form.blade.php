<div class="mx-auto max-w-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">{{ $role && $role->exists ? 'Edit Role' : 'Create Role' }}</h1>
        <a href="{{ route('roles.index') }}" class="px-3 py-2 rounded bg-gray-200 text-gray-800">Back</a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-50 text-green-800 px-3 py-2">{{ session('status') }}</div>
    @endif

    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" wire:model.live="name" class="w-full rounded border px-3 py-2" />
            @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Permissions (JSON)</label>
            <textarea wire:model.live="permissionsInput" rows="10" class="w-full rounded border px-3 py-2 font-mono"></textarea>
            @error('permissionsInput') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-500 mt-1">Example: {"can_edit": true, "scopes": ["users","reports"]}</p>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Save</button>
            <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded bg-gray-100">Cancel</a>
        </div>
    </form>
</div>
