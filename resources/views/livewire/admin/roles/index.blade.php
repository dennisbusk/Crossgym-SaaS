<div class="mx-auto max-w-4xl p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Roles</h1>
        <a href="{{ route('roles.create') }}" class="px-3 py-2 rounded bg-blue-600 text-white">Create role</a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-50 text-green-800 px-3 py-2">{{ session('status') }}</div>
    @endif

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Search..." class="w-full rounded border px-3 py-2" />
    </div>

    <div class="overflow-x-auto rounded border">
        <table class="min-w-full text-left">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Users</th>
                    <th class="px-4 py-2 w-40">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($roles as $role)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $role->name }}</td>
                    <td class="px-4 py-2">{{ $role->users()->count() }}</td>
                    <td class="px-4 py-2 flex gap-2">
                        <a href="{{ route('roles.edit', $role) }}" class="px-2 py-1 rounded bg-gray-800 text-white">Edit</a>
                        <button wire:click="delete({{ $role->id }})" class="px-2 py-1 rounded bg-red-600 text-white" onclick="return confirm('Delete this role?')">Delete</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-gray-500">No roles found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $roles->links() }}</div>
</div>
