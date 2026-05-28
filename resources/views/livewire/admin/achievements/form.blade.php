<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">{{ $achievement && $achievement->exists ? __('Edit Achievement') : __('New Achievement') }}</h1>
        <flux:button tag="a" href="{{ route('achievements.index') }}" variant="ghost" icon="arrow-left">{{ __('Back to list') }}</flux:button>
    </div>

    <x-banners/>

    <form wire:submit="save" class="space-y-8">
        <flux:card class="space-y-6">
            <h2 class="text-lg font-medium">{{ __('Basic Information') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input label="{{ __('Name (Danish)') }}" wire:model.blur="name.da" required />
                <flux:input label="{{ __('Slug') }}" wire:model="slug" required />

                <div class="md:col-span-2">
                    <flux:textarea label="{{ __('Description (Danish)') }}" wire:model="description.da" />
                </div>

                <flux:input label="{{ __('Icon (Heroicon name)') }}" wire:model="icon" placeholder="star" />
                <flux:select label="{{ __('Rarity') }}" wire:model="rarity">
                    <flux:select.option value="common">{{ __('Common') }}</flux:select.option>
                    <flux:select.option value="rare">{{ __('Rare') }}</flux:select.option>
                    <flux:select.option value="epic">{{ __('Epic') }}</flux:select.option>
                    <flux:select.option value="legendary">{{ __('Legendary') }}</flux:select.option>
                </flux:select>

                <flux:input type="number" label="{{ __('XP Points') }}" wire:model="points" required />
                <flux:select label="{{ __('Type') }}" wire:model="type">
                    <flux:select.option value="count">{{ __('Count') }}</flux:select.option>
                    <flux:select.option value="streak">{{ __('Streak') }}</flux:select.option>
                    <flux:select.option value="time_window">{{ __('Time Window') }}</flux:select.option>
                    <flux:select.option value="category_count">{{ __('Category Count') }}</flux:select.option>
                </flux:select>

                <div class="flex items-center gap-4">
                    <flux:checkbox label="{{ __('Hidden') }}" wire:model="hidden" />
                    <flux:checkbox label="{{ __('Repeatable') }}" wire:model="repeatable" />
                    <flux:checkbox label="{{ __('Active') }}" wire:model="is_active" />
                </div>
            </div>
        </flux:card>

        <flux:card class="space-y-6">
            <h2 class="text-lg font-medium">{{ __('Rules & Triggers') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:select label="{{ __('Event Trigger') }}" wire:model="event">
                    <flux:select.option value="user.checked_in">{{ __('Check-in') }} (user.checked_in)</flux:select.option>
                    <flux:select.option value="user.completed_workout">{{ __('Completed Workout') }} (user.completed_workout)</flux:select.option>
                    <flux:select.option value="user.booked_class">{{ __('Booked Class') }} (user.booked_class)</flux:select.option>
                    <flux:select.option value="user.registered">{{ __('User Registered') }} (user.registered)</flux:select.option>
                </flux:select>

                <flux:select label="{{ __('Operator') }}" wire:model="operator">
                    <flux:select.option value=">=">{{ __('Greater than or equal') }} (>=)</flux:select.option>
                    <flux:select.option value="==">{{ __('Equals') }} (==)</flux:select.option>
                </flux:select>

                <flux:input label="{{ __('Target Value') }}" wire:model="target" required />
            </div>

            <p class="text-sm text-gray-500">
                {{ __('For count achievements, the target is the number of occurrences.') }}<br>
                {{ __('For streak achievements, the target is the number of consecutive days.') }}
            </p>
        </flux:card>

        <div class="flex justify-end gap-2">
            <flux:button tag="a" href="{{ route('achievements.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Save Achievement') }}</flux:button>
        </div>
    </form>
</div>
