<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
        <h1 class="text-2xl font-semibold">{{ __('Roles') }}</h1>
        </div>
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <x-flowbite.button class="hover:cursor-pointer" wire:click="export" variant="ghost">
                    {{ __('Export') }}
                </x-flowbite.button>
                <x-flowbite.link href="{{ route('roles.create') }}" variant="ghost">
                    {{ __('New Role') }}
                </x-flowbite.link>
            </div>
        </div>
    </div>
    
    <x-banners/>
    
    <div class="relative overflow-x-auto ">
        
        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.cell>{{ __('Name') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Users') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>
            <x-flowbite.table.body>
                @forelse ($roles as $role)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $role->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $role->users()->count() }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            <flux:button icon="eye" tag="a" href="{{ route('roles.show', $role) }}" variant="ghost" />
                            <flux:button icon="lock-closed" tag="a" href="{{ route('roles.permissions', $role) }}" variant="ghost"/>
                            <flux:button icon="pencil-square" tag="a" href="{{ route('roles.edit', $role) }}" variant="ghost" />
                            <flux:button icon="trash" wire:click="delete({{ $role->id }})" variant="ghost" />
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                        <x-flowbite.table.body.cell colspan="3" class="text-center">{{ __('No roles found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>

    </div>
</div>
