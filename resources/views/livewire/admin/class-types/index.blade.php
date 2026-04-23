<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('Class Types') }}</h1>
        </div>
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <flux:button class="hover:cursor-pointer" wire:click="export" variant="ghost" icon="document-arrow-down" wire:loading.attr="disabled">
                    {{ __('Export') }}
                </flux:button>
                <flux:button tag="a" href="{{ route('class-types.create') }}" variant="ghost" icon="plus">
                    {{ __('New Class Type') }}
                </flux:button>
            </div>
        </div>
    </div>

    <x-banners/>

    <div class="relative overflow-x-auto ">

        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="name" :$sortField :$sortDirection>{{ __('Name') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>
            <x-flowbite.table.body>
                @forelse ($classTypes as $type)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $type->getTranslation('name', app()->getLocale()) }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            <flux:button icon="eye" tag="a" href="{{ route('class-types.show', $type) }}" variant="ghost" />
                            <flux:button icon="pencil-square" tag="a" href="{{ route('class-types.edit', $type) }}" variant="ghost" />
                            <flux:button icon="trash" wire:click="delete({{ $type->id }})" variant="ghost" />
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                        <x-flowbite.table.body.cell colspan="3" class="text-center">{{ __('No class types found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div class="mt-4">
        {{ $classTypes->links() }}
    </div>
</div>
