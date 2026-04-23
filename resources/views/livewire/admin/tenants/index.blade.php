<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('Tenants') }}</h1>
        </div>
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <flux:button class="hover:cursor-pointer" wire:click="export" variant="ghost" icon="document-arrow-down" wire:loading.attr="disabled">
                    {{ __('Export') }}
                </flux:button>
                <flux:button tag="a" href="{{ route('tenants.create') }}" variant="ghost" icon="plus">
                    {{ __('New Tenant') }}
                </flux:button>
            </div>
        </div>
    </div>

    <x-banners/>

    <div class="relative overflow-x-auto ">

        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="id" :$sortField :$sortDirection>{{ __('ID') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="name" :$sortField :$sortDirection>{{ __('Name') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="domain" :$sortField :$sortDirection>{{ __('Domain') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>

            <x-flowbite.table.body>
                @forelse ($tenants as $tenant)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $tenant->id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $tenant->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $tenant->domain }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            <flux:button icon="eye" tag="a" href="{{ route('tenants.show', $tenant) }}" variant="ghost" />
                            <flux:button icon="pencil-square" tag="a" href="{{ route('tenants.edit', $tenant) }}" variant="ghost" />
                            <flux:button icon="trash" wire:click="delete({{ $tenant->id }})" variant="ghost" />
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                        <x-flowbite.table.body.cell class="w-4 p-4" colspan="4">{{ __('No tenants found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div class="mt-4">
        {{ $tenants->links() }}
    </div>
</div>
