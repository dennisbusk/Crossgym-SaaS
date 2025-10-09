<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Users') }}</h1>
    </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif
    
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <flux:button class="hover:cursor-pointer" wire:click="export" variant="primary">
                    {{ __('Export') }}
                </flux:button>
                <flux:button tag="a" href="{{ route('users.create') }}" variant="primary">
                    {{ __('New User') }}
                </flux:button>
            </div>
        </div>
        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.cell>{{ __('ID') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Name') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Email') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Role') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Tenant') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>
            
            <x-flowbite.table.body>
                @forelse ($users as $user)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $user->id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $user->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $user->email }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $user->role?->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $user->tenant?->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            <flux:button icon="eye" tag="a" href="{{ route('users.show', $user) }}" variant="ghost"/>
                            <flux:button icon="lock-closed" tag="a" href="{{ route('users.permissions', $user) }}" variant="ghost"/>
                            <flux:button icon="pencil-square" tag="a" href="{{ route('users.edit', $user) }}" variant="ghost"/>
                            <flux:button icon="trash" wire:click="delete({{ $user->id }})" variant="ghost"/>
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                        <x-flowbite.table.body.cell class="w-4 p-4" colspan="6">{{ __('No users found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>
</div>
