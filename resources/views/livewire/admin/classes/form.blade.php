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
            <label class="block text-sm font-medium">{{ __('Description') }}</label>
            <textarea wire:model.defer="description" class="mt-1 w-full rounded-md border px-3 py-2"></textarea>
        </div>

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

        <div class="flex gap-2 justify-end">
            <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                {{ $gymClass?->exists ? __('Update') : __('Create') }}
            </button>
            <a href="{{ route('classes.index') }}" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 hover:bg-yellow-800">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
