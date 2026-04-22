<div class="w-full">
    <label for="tenant-select" class="sr-only">{{ __('Select Tenant') }}</label>
    <select
        id="tenant-select"
        wire:model.live="selectedTenant"
        wire:change="switchTenant"
        class="block w-full rounded-lg border border-gray-300 bg-white
               px-4 py-2 text-sm text-gray-900
               focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50
               shadow-sm hover:border-gray-400
               dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600 dark:focus:border-blue-400
               dark:focus:ring-blue-900
               transition"
    >
        @foreach($tenants as $t)
            <option value="{{ $t->id }}">
                {{ $t->name }}
            </option>
        @endforeach
    </select>
</div>
