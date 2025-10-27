<div class="space-y-6 max-w-3xl">
    <h1 class="text-2xl font-semibold">{{ $plan && $plan->exists ? __('Edit Plan') : __('Create Plan') }}</h1>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-50 text-green-800 px-3 py-2">{{ __(session('status')) }}</div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Name') }}</label>
                <input type="text" wire:model.live="name" class="w-full rounded border px-3 py-2" />
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Plan type') }}</label>
                <select wire:model.live="plan_type" class="w-full rounded border px-3 py-2">
                    <option value="subscription">{{ __('Subscription') }}</option>
                    <option value="one_off">{{ __('One-off') }}</option>
                </select>
                @error('plan_type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Amount') }}</label>
                <input type="text" wire:model.live="amount" class="w-full rounded border px-3 py-2" placeholder="199.00" />
                @error('amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Currency') }}</label>
                <input type="text" wire:model.live="currency" class="w-full rounded border px-3 py-2 uppercase" placeholder="DKK" />
                @error('currency') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div x-data="{ type: @entangle('plan_type'), val: @entangle('interval') }">
                <label class="block text-sm font-medium mb-1">{{ __('Interval') }}</label>
                <template x-if="type === 'subscription'">
                    <select x-model="val" @change="$wire.set('interval', val)" class="w-full rounded border px-3 py-2">
                        <option value="day">{{ __('Day') }}</option>
                        <option value="week">{{ __('Week') }}</option>
                        <option value="month">{{ __('Month') }}</option>
                        <option value="year">{{ __('Year') }}</option>
                    </select>
                </template>
                <template x-if="type === 'one_off'">
                    <input type="text" class="w-full rounded border px-3 py-2 bg-gray-100" value="{{ __('One-time') }}" disabled />
                </template>
                @error('interval') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Weekly booking limit') }}</label>
                <input type="number" min="0" wire:model.live="weekly_booking_limit" class="w-full rounded border px-3 py-2" />
                @error('weekly_booking_limit') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Total booking credits') }}</label>
                <input type="number" min="0" wire:model.live="total_booking_credits" class="w-full rounded border px-3 py-2" />
                @error('total_booking_credits') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">{{ __('Allowed class types') }}</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($classTypes as $ct)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" value="{{ $ct['id'] }}" wire:model.live="allowed_class_type_ids" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ $ct['name'] }}</span>
                    </label>
                @endforeach
            </div>
            @error('allowed_class_type_ids') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-2 justify-end">
            <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">
                {{ $plan?->exists ? __('Update') : __('Create') }}
            </button>
            <a href="{{ route('plans.index') }}" class="inline-flex items-center rounded-md bg-yellow-600 px-3 py-2 hover:bg-yellow-800">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
