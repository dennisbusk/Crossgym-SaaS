<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Roles') }}</h1>
    
    </div>
    
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif
    
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <x-flowbite.button class="hover:cursor-pointer" wire:click="export" variant="primary">
                    {{ __('Export') }}
                </x-flowbite.button>
                <x-flowbite.button tag="a" href="{{ route('roles.create') }}" variant="primary">
                    {{ __('New Role') }}
                </x-flowbite.button>
            </div>
        </div>
        <x-flowbite.table>
        <x-flowbite.table.head>
        <x-flowbite.table.head.row>
            <x-flowbite.table.head.cell class="p-4">
                <div class="flex items-center">
                    <input id="checkbox-all-search" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="checkbox-all-search" class="sr-only">checkbox</label>
                </div>
            </x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell >{{ __('Name') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell >{{ __('Users') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
        </x-flowbite.table.head.row>
        </x-flowbite.table.head>
        <x-flowbite.table.body>
        @forelse ($roles as $role)
            <x-flowbite.table.body.row>
                <x-flowbite.table.body.cell class="w-4 p-4">
                    <div class="flex items-center">
                        <input id="checkbox-table-search-1" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkbox-table-search-1" class="sr-only">checkbox</label>
                    </div>
                </x-flowbite.table.body.cell>
                <x-flowbite.table.body.cell>{{ $role->name }}</x-flowbite.table.body.cell>
                <x-flowbite.table.body.cell>{{ $role->users()->count() }}</x-flowbite.table.body.cell>
                <x-flowbite.table.body.cell class="text-right space-x-2">
                    <flux:button icon="eye" tag="a" href="{{ route('roles.show', $role) }}" variant="ghost" />
                    <flux:button icon="pencil-square" tag="a" href="{{ route('roles.edit', $role) }}" variant="ghost" />
                    <flux:button icon="trash" wire:click="delete({{ $role->id }})" variant="ghost" />
                </x-flowbite.table.body.cell>
            </x-flowbite.table.body.row>
        @empty
            <x-flowbite.table.body.row>
                <x-flowbite.table.body.cell colspan="3" class="text-center">{{ __('No roles found.') }}</x-flowbite.table.body.cell>
            </x-flowbite.table.body.row>
        @endforelse
        </x-flowbite.table.body>
    </x-flowbite.table>

</div>
