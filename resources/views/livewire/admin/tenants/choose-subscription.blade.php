<div class="space-y-6" x-data="{ selected: @entangle('selected').live }">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('Choose subscription model') }}</h1>
        </div>
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <x-flowbite.button wire:click="confirm" x-bind:disabled="!selected" variant="primary">
                    {{ __('Confirm choice') }}
                </x-flowbite.button>
            </div>
        </div>
    </div>

    <x-banners/>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($options as $option)
            <div class="border rounded-md p-4 flex flex-col gap-2 bg-white dark:bg-gray-800" x-bind:class="{ 'ring-2 ring-blue-500': selected == {{ $option->id }} }">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-lg font-semibold">{{ __($option->name) }}</div>
                        <div class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $option->type)) }} — {{ $option->type === 'transaction_fee' ? $option->value . '%' : $option->value . ' kr' }}</div>
                    </div>
                    <flux:button icon="check" variant="ghost" class="ml-2" x-bind:class="{ 'text-blue-600': selected == {{ $option->id }} }" wire:click="select({{ $option->id }})" />
                </div>
            </div>
        @endforeach
    </div>
</div>
