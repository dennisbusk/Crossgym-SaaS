<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Tenants</h1>
        <a href="{{ route('tenants.create') }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">Create Tenant</a>
    </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto rounded-md border">
        <table class="min-w-full divide-y">
            <thead>
            <tr class="text-left">
                <th class="px-4 py-2">ID</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Domain</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @forelse ($tenants as $tenant)
                <tr>
                    <td class="px-4 py-2">{{ $tenant->id }}</td>
                    <td class="px-4 py-2">{{ $tenant->name }}</td>
                    <td class="px-4 py-2">{{ $tenant->domain }}</td>
                    <td class="px-4 py-2 space-x-2">
                        <a href="{{ route('tenants.edit', $tenant) }}" class="text-blue-600 hover:underline">Edit</a>
                        <button wire:click="delete({{ $tenant->id }})" class="text-red-600 hover:underline">Delete</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-4 py-4" colspan="4">No tenants found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
