<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('Workout Log') }}</h1>
        </div>
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <flux:button class="hover:cursor-pointer" wire:click="export" variant="ghost" icon="document-arrow-down" wire:loading.attr="disabled">
                    <span class="hidden sm:inline">{{ __('Export') }}</span>
                </flux:button>
                <flux:button icon="plus" href="{{ route('workout-logs.create') }}" variant="ghost" tag="a">
                    <span class="hidden sm:inline">{{ __('New Entry') }}</span>
                </flux:button>
            </div>
        </div>
    </div>

    <x-banners/>

    <div class="flex justify-between items-center mb-4">
        <div class="w-full md:w-64">
            <flux:input placeholder="{{ __('Search...') }}" wire:model.live="search" icon="magnifying-glass" />
        </div>
    </div>

    <div class="relative overflow-x-auto">
        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="date" :$sortField :$sortDirection wire:click="sortBy('date')">{{ __('Date') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell>{{ __('Exercise') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Details') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>

            <x-flowbite.table.body>
                @forelse ($logs as $log)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $log->date->format('d.m.Y') }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <span class="font-medium">{{ $log->exercise?->name }}</span>
                            @if($log->exercise)
                                <span class="text-xs text-zinc-500">({{ __($log->exercise->category) }})</span>
                            @endif
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <div class="text-sm">
                                @if($log->exercise?->category === 'strength')
                                    {{ $log->sets }} x {{ $log->reps }} @ {{ $log->weight }} kg
                                @elseif($log->exercise?->category === 'cardio')
                                    {{ $log->distance }} km / {{ floor($log->duration / 60) }} min
                                @elseif($log->exercise?->category === 'biometric')
                                    {{ $log->weight }} kg / {{ __('Mood') }}: {{ $log->mood }}
                                @endif
                                @if($log->intensity)
                                    <div class="text-xs text-zinc-500">{{ __('Intensity') }}: {{ $log->intensity }}/10</div>
                                @endif
                            </div>
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right">
                            <div class="flex justify-end items-center gap-2">
                                <div class="hidden sm:flex items-center gap-2">
                                    <flux:button icon="eye" tag="a" href="{{ route('workout-logs.show', $log) }}" variant="ghost" />
                                    <flux:button icon="pencil-square" tag="a" href="{{ route('workout-logs.edit', $log) }}" variant="ghost" />
                                </div>

                                <flux:dropdown align="end" aria-label="{{ __('Actions') }}">
                                    <flux:button icon="ellipsis-horizontal" variant="ghost" />

                                    <flux:menu>
                                        <div class="sm:hidden">
                                            <flux:menu.item icon="eye" tag="a" href="{{ route('workout-logs.show', $log) }}">{{ __('Show') }}</flux:menu.item>
                                            <flux:menu.item icon="pencil-square" tag="a" href="{{ route('workout-logs.edit', $log) }}">{{ __('Edit') }}</flux:menu.item>
                                            <flux:menu.separator />
                                        </div>
                                        <flux:menu.item icon="trash" wire:click="delete({{ $log->id }})" variant="danger" wire:confirm="{{ __('Are you sure?') }}">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <x-flowbite.table.body.cell class="w-4 p-4" colspan="4">{{ __('No entries found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
