<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Classes') }}</h1>
        <flux:input placeholder="{{ __('Search...') }}" wire:model.live="search" />
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
                <x-flowbite.button tag="a" href="{{ route('classes.create') }}" variant="primary">
                    {{ __('New Class') }}
                </x-flowbite.button>
            </div>
        </div>
        <x-flowbite.table>
            <x-flowbite.table.head>
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.cell>{{ __('Name') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Type') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Trainer') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Start') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('End') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Participants') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>
            <x-flowbite.table.body>
                @forelse ($classes as $class)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $class->getTranslation('name', app()->getLocale()) }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $class->classType?->getTranslation('name', app()->getLocale()) }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $class->trainer?->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ optional($class->class_start)->format('Y-m-d H:i') }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ optional($class->class_end)->format('Y-m-d H:i') }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            {{ $class->participants_count }} / {{ $class->max_participants ?? '∞' }}
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            <flux:button icon="eye" tag="a" href="{{ route('classes.show', $class) }}" variant="ghost" />
                            <flux:button icon="pencil-square" tag="a" href="{{ route('classes.edit', $class) }}" variant="ghost" />
                            <flux:button icon="trash" wire:click="delete({{ $class->id }})" variant="ghost" />
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell colspan="7" class="text-center">{{ __('No classes found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>
</div>
