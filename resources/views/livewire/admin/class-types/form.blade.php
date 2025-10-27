<div class="space-y-6 max-w-2xl">
    <h1 class="text-2xl font-semibold">{{ $classType?->exists ? __('Edit Class Type') : __('Create Class Type') }}</h1>

    <x-banners/>

    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">{{ __('Name') }}</label>
            <input type="text" wire:model.defer="name" class="mt-1 w-full rounded-md border px-3 py-2" />
            @error('name')
            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">{{ __('Description') }}</label>
            <textarea wire:model.defer="description" class="mt-1 w-full rounded-md border px-3 py-2"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium">{{ __('Slug') }}</label>
                <input type="text" wire:model.defer="slug" class="mt-1 w-full rounded-md border px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Color') }}</label>
                <input type="text" wire:model.defer="color" class="mt-1 w-full rounded-md border px-3 py-2" placeholder="#000000" />
            </div>
            <div>
                <label class="block text-sm font-medium">{{ __('Image URL') }}</label>
                <input type="text" wire:model.defer="image" class="mt-1 w-full rounded-md border px-3 py-2" />
            </div>
        </div>

        <div class="flex gap-2 justify-end">
            <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                {{ $classType?->exists ? __('Update') : __('Create') }}
            </button>
            <a href="{{ route('class-types.index') }}" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 hover:bg-yellow-800">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
