<div class="mx-auto max-w-7xl p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ __('View Role') }}</h1>
        <div class="space-x-2">
            <flux:button tag="a" href="{{ route('roles.index') }}" variant="ghost">{{ __('Back') }}</flux:button>
            <flux:button tag="a" href="{{ route('roles.edit', $role) }}" variant="ghost">{{ __('Edit') }}</flux:button>
        </div>
    </div>

    <div class="rounded border px-4 py-3 space-y-3">
        <div>
            <div class="text-sm text-gray-500">{{ __('Name') }}</div>
            <div class="font-medium">{{ $role->name }}</div>
        </div>
        <div>
            <div class="text-sm text-gray-500">{{ __('Users count') }}</div>
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

    <div class="space-y-4">
        <h2 class="text-xl font-semibold">{{ __('Users with this role') }}</h2>

        <div class="relative overflow-x-auto">
            <x-flowbite.table>
                <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <x-flowbite.table.head.row>
                        <x-flowbite.table.head.cell>{{ __('ID') }}</x-flowbite.table.head.cell>
                        <x-flowbite.table.head.cell>{{ __('Name') }}</x-flowbite.table.head.cell>
                        <x-flowbite.table.head.cell>{{ __('Email') }}</x-flowbite.table.head.cell>
                        <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                    </x-flowbite.table.head.row>
                </x-flowbite.table.head>

                <x-flowbite.table.body>
                    @forelse ($users as $user)
                        <x-flowbite.table.body.row>
                            <x-flowbite.table.body.cell>{{ $user->id }}</x-flowbite.table.body.cell>
                            <x-flowbite.table.body.cell>{{ $user->name }}</x-flowbite.table.body.cell>
                            <x-flowbite.table.body.cell>{{ $user->email }}</x-flowbite.table.body.cell>
                            <x-flowbite.table.body.cell class="text-right space-x-2">
                                @can('impersonate', $user)
                                    <flux:button icon="identification" wire:click="impersonate({{ $user->id }})" variant="ghost" />
                                @endcan
                                <flux:button icon="eye" tag="a" href="{{ route('users.show', $user) }}" variant="ghost" />
                                <flux:button icon="pencil-square" tag="a" href="{{ route('users.edit', $user) }}" variant="ghost" />
                            </x-flowbite.table.body.cell>
                        </x-flowbite.table.body.row>
                    @empty
                        <x-flowbite.table.body.row>
                            <x-flowbite.table.body.cell colspan="4" class="text-center py-4 text-gray-500">
                                {{ __('No users found with this role.') }}
                            </x-flowbite.table.body.cell>
                        </x-flowbite.table.body.row>
                    @endforelse
                </x-flowbite.table.body>
            </x-flowbite.table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
