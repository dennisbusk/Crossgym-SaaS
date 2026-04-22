<div class="space-y-6 max-w-2xl">
    <h1 class="text-2xl font-semibold">{{ __('AI Coach Settings') }}</h1>

    <x-banners/>

    <form wire:submit="save" class="space-y-6">
        <div>
            <label class="block text-sm font-medium mb-2">{{ __('Equipment') }}</label>
            <div class="flex gap-2 mb-2">
                <flux:input wire:model="newEquipment" placeholder="{{ __('Add equipment') }}" wire:keydown.enter.prevent="addEquipment" />
                <flux:button type="button" variant="ghost" wire:click="addEquipment">{{ __('Add') }}</flux:button>
            </div>
            <ul class="flex flex-wrap gap-2">
                @foreach($equipment as $index => $item)
                    <li class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-700 px-3 py-1">
                        <span>{{ $item }}</span>
                        <button type="button" wire:click="removeEquipment({{ $index }})" class="text-zinc-500 hover:text-red-600">&times;</button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">{{ __('Intensity options') }}</label>
            <div class="flex gap-2 mb-2">
                <flux:input wire:model="newIntensity" placeholder="{{ __('Add intensity') }}" wire:keydown.enter.prevent="addIntensity" />
                <flux:button type="button" variant="ghost" wire:click="addIntensity">{{ __('Add') }}</flux:button>
            </div>
            <ul class="flex flex-wrap gap-2">
                @foreach($intensity as $index => $item)
                    <li class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-700 px-3 py-1">
                        <span>{{ $item }}</span>
                        <button type="button" wire:click="removeIntensity({{ $index }})" class="text-zinc-500 hover:text-red-600">&times;</button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">{{ __('Focus area options') }}</label>
            <div class="flex gap-2 mb-2">
                <flux:input wire:model="newFocusArea" placeholder="{{ __('Add focus area') }}" wire:keydown.enter.prevent="addFocusArea" />
                <flux:button type="button" variant="ghost" wire:click="addFocusArea">{{ __('Add') }}</flux:button>
            </div>
            <ul class="flex flex-wrap gap-2">
                @foreach($focus_area as $index => $item)
                    <li class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-700 px-3 py-1">
                        <span>{{ $item }}</span>
                        <button type="button" wire:click="removeFocusArea({{ $index }})" class="text-zinc-500 hover:text-red-600">&times;</button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">{{ __('Difficulty options') }}</label>
            <div class="flex gap-2 mb-2">
                <flux:input wire:model="newDifficulty" placeholder="{{ __('Add difficulty') }}" wire:keydown.enter.prevent="addDifficulty" />
                <flux:button type="button" variant="ghost" wire:click="addDifficulty">{{ __('Add') }}</flux:button>
            </div>
            <ul class="flex flex-wrap gap-2">
                @foreach($difficulty as $index => $item)
                    <li class="inline-flex items-center gap-1 rounded-full bg-zinc-100 dark:bg-zinc-700 px-3 py-1">
                        <span>{{ $item }}</span>
                        <button type="button" wire:click="removeDifficulty({{ $index }})" class="text-zinc-500 hover:text-red-600">&times;</button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">{{ __('Default duration (min)') }}</label>
                <flux:input type="number" wire:model="duration_min" min="5" max="90" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Max duration (min)') }}</label>
                <flux:input type="number" wire:model="duration_max" min="5" max="90" />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
        </div>
    </form>
</div>
