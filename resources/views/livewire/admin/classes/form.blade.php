<div class="space-y-6 max-w-3xl">
    <h1 class="text-2xl font-semibold">{{ $gymClass?->exists ? __('Edit Class') : __('Create Class') }}</h1>

    <x-banners/>

    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">{{ __('Name') }}</label>
                <input type="text" wire:model.defer="name" class="mt-1 w-full rounded-md border px-3 py-2" />
                @error('name')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Type') }}</label>
                <select wire:model.defer="class_type_id" class="mt-1 w-full rounded-md border px-3 py-2">
                    <option value="">-- {{ __('Select') }} --</option>
                    @foreach($classTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->getTranslation('name', app()->getLocale()) }}</option>
                    @endforeach
                </select>
                @error('class_type_id')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div>
            <div class="flex justify-between items-center mb-1">
                <label class="block text-sm font-medium">{{ __('Description') }}</label>
                <span x-data>
                    <flux:button variant="ghost" size="sm" type="button" x-on:click="$dispatch('modal-show', { name: 'ai-coach-modal' })">
                        {{ __('Generate with AI Coach') }}
                    </flux:button>
                </span>
            </div>
            <textarea wire:model.defer="description" class="mt-1 w-full rounded-md border px-3 py-2 min-h-[120px]"></textarea>
        </div>

        @livewire('components.ai-coach-modal')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium">{{ __('Trainer') }}</label>
                <select wire:model.defer="trainer_id" class="mt-1 w-full rounded-md border px-3 py-2">
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                    @endforeach
                </select>
                @error('trainer_id')
                <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Max participants') }}</label>
                <input type="number" wire:model.defer="max_participants" class="mt-1 w-full rounded-md border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Start') }}</label>
                <input type="datetime-local" wire:model.defer="class_start" class="mt-1 w-full rounded-md border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('End') }}</label>
                <input type="datetime-local" wire:model.defer="class_end" class="mt-1 w-full rounded-md border px-3 py-2" />
            </div>
        </div>

        <div class="space-y-4 border-t pt-4">
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium">{{ __('Color') }}</label>
                    <select wire:model.defer="color_id" class="mt-1 w-full rounded-md border px-3 py-2">
                        <option value="">-- {{ __('Select Color') }} --</option>
                        @foreach($colors as $color)
                            <option value="{{ $color->id }}" style="background-color: {{ $color->color }}; color: {{ $this->getContrastColor($color->color) }}">
                                {{ $color->name }} ({{ $color->color }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <flux:button type="button" variant="ghost" icon="plus" wire:click="$toggle('showNewColorForm')">
                    {{ __('New Color') }}
                </flux:button>
            </div>

            @if($showNewColorForm)
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 space-y-3">
                    <h3 class="text-sm font-semibold">{{ __('Create New Color') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium">{{ __('Color Name') }}</label>
                            <input type="text" wire:model.defer="newColorName" class="mt-1 w-full rounded-md border px-2 py-1 text-sm" placeholder="e.g. WOD, Hybrid..." />
                            @error('newColorName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium">{{ __('Hex Code') }}</label>
                            <div class="flex gap-2">
                                <input type="color" wire:model.live="newColorHex" class="mt-1 h-8 w-12 rounded border p-1" />
                                <input type="text" wire:model.defer="newColorHex" class="mt-1 flex-1 rounded-md border px-2 py-1 text-sm" />
                            </div>
                            @error('newColorHex') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <flux:button size="sm" variant="ghost" wire:click="$set('showNewColorForm', false)">{{ __('Cancel') }}</flux:button>
                        <flux:button size="sm" variant="primary" wire:click="createColor">{{ __('Save Color') }}</flux:button>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex gap-2 justify-end">
            <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                {{ $gymClass?->exists ? __('Update') : __('Create') }}
            </button>
            <a href="{{ route('classes.index') }}" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 hover:bg-yellow-800">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
