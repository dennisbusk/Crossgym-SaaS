<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('Classes') }}</h1>
        </div>
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <x-flowbite.button class="hover:cursor-pointer" wire:click="export" variant="ghost">
                    {{ __('Export') }}
                </x-flowbite.button>
                <x-flowbite.link href="{{ route('classes.create') }}" variant="ghost">
                    {{ __('New Class') }}
                </x-flowbite.link>
            </div>
        </div>
    </div>
    <x-banners/>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <flux:input label="{{ __('Search') }}" placeholder="{{ __('Search...') }}" wire:model.live="search" icon="magnifying-glass" />
        <flux:input type="date" label="{{ __('From') }}" wire:model.live="fromDate" />
        <flux:input type="date" label="{{ __('To') }}" wire:model.live="toDate" />
        <flux:select label="{{ __('Trainer') }}" wire:model.live="trainerId" placeholder="{{ __('Trainer') }}">
            <flux:select.option value="">{{ __('All Trainers') }}</flux:select.option>
            @foreach($trainers as $trainer)
                <flux:select.option value="{{ $trainer->id }}">{{ $trainer->name }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select label="{{ __('Class Type') }}" wire:model.live="classTypeId" placeholder="{{ __('Class Type') }}">
            <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
            @foreach($classTypes as $type)
                <flux:select.option value="{{ $type->id }}">{{ $type->getTranslation('name', app()->getLocale()) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="relative overflow-x-auto ">

        <x-flowbite.table>
            <x-flowbite.table.head>
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="name" :$sortField :$sortDirection>{{ __('Name') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell>{{ __('Type') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Trainer') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.sortable field="class_start" :$sortField :$sortDirection>{{ __('Start') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="class_end" :$sortField :$sortDirection>{{ __('End') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="participants_count" :$sortField :$sortDirection>{{ __('Participants') }}</x-flowbite.table.head.sortable>
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
                            @can('update', $class)
                                <flux:button icon="pencil-square" tag="a" href="{{ route('classes.edit', $class) }}" variant="ghost" />
                            @endcan
                            @can('delete', $class)
                                <flux:button icon="trash" wire:click="delete({{ $class->id }})" variant="ghost" />
                            @endcan
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

    <div class="mt-4">
        {{ $classes->links() }}
    </div>
</div>
