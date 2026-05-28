<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Achievements') }}</h1>
        <div class="flex items-center gap-2">
            <flux:button tag="a" href="{{ route('achievements.create') }}" variant="ghost" icon="plus">
                <span class="hidden sm:inline">{{ __('New Achievement') }}</span>
            </flux:button>
        </div>
    </div>

    <x-banners/>

    <div class="flex items-center mb-6">
        <flux:input label="{{ __('Search') }}" wire:model.live="search" placeholder="{{ __('Search achievements...') }}" icon="magnifying-glass" />
    </div>

    <div class="relative overflow-x-auto overflow-y-visible">
        <x-flowbite.table :useDataTables="false">
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="id" :$sortField :$sortDirection>{{ __('ID') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell>{{ __('Name') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Type') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Points') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Rarity') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Status') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>

            <x-flowbite.table.body>
                @forelse ($achievements as $achievement)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $achievement->id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <div class="flex items-center gap-2">
                                @if($achievement->icon)
                                    <flux:icon :name="$achievement->icon" variant="mini" />
                                @endif
                                <span>{{ $achievement->name }}</span>
                            </div>
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ ucfirst($achievement->type) }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $achievement->points }} XP</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                             <flux:badge :color="match($achievement->rarity) {
                                'common' => 'gray',
                                'rare' => 'blue',
                                'epic' => 'purple',
                                'legendary' => 'orange',
                                default => 'gray'
                             }">{{ ucfirst($achievement->rarity) }}</flux:badge>
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <flux:badge :color="$achievement->is_active ? 'green' : 'red'">
                                {{ $achievement->is_active ? __('Active') : __('Inactive') }}
                            </flux:badge>
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right">
                            <div class="flex justify-end items-center gap-2">
                                <div class="hidden sm:flex items-center gap-2">
                                    <flux:button icon="pencil-square" tag="a" href="{{ route('achievements.edit', $achievement) }}" variant="ghost" />
                                </div>

                                <flux:dropdown align="end" aria-label="{{ __('Actions') }}">
                                    <flux:button icon="ellipsis-horizontal" variant="ghost" />

                                    <flux:menu>
                                        <div class="sm:hidden">
                                            <flux:menu.item icon="pencil-square" tag="a" href="{{ route('achievements.edit', $achievement) }}">{{ __('Edit') }}</flux:menu.item>
                                        </div>
                                        <flux:menu.item icon="trash" wire:click="delete({{ $achievement->id }})" variant="danger" wire:confirm="{{ __('Are you sure you want to delete this achievement?') }}">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell colspan="7" class="text-center py-4">{{ __('No achievements found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div class="mt-4">
        {{ $achievements->links() }}
    </div>
</div>
