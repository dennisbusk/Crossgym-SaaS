<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Plan details') }}</h1>
        <div class="flex items-center gap-2">
            @can('update', $plan)
                <x-flowbite.link href="{{ route('plans.edit', $plan) }}" variant="primary">{{ __('Edit') }}</x-flowbite.link>
            @endcan
            <x-flowbite.link href="{{ route('plans.index') }}" variant="ghost">{{ __('Back') }}</x-flowbite.link>
        </div>
    </div>

    <x-banners />

    <div class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-500">{{ __('Name') }}</div>
                <div class="font-medium">{{ $plan->name }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Interval') }}</div>
                <div class="font-medium">{{ $plan->interval }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Price') }}</div>
                <div class="font-medium">
                    @php $amount = is_null($plan->amount) ? null : number_format(((int) $plan->amount) / 100, 2, ',', '.'); @endphp
                    {{ $amount ? $amount . ' ' . strtoupper((string) $plan->currency) : __('N/A') }}
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Stripe Price ID') }}</div>
                <div class="font-mono text-xs">{{ $plan->stripe_price_id }}</div>
            </div>
        </div>

        <div>
            <div class="text-sm text-gray-500 mb-1">{{ __('Allowed class types') }}</div>
            @if(count($allowedClassTypes))
                <ul class="list-disc ms-5">
                    @foreach($allowedClassTypes as $ct)
                        <li>{{ $ct['name'] }}</li>
                    @endforeach
                </ul>
            @else
                <div class="text-gray-600">{{ __('None') }}</div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex justify-between items-center mb-2">
            <h2 class="text-xl font-semibold">{{ __('Users on this plan') }}</h2>
            <div class="flex items-center gap-2">
                <flux:button wire:click="export" variant="primary">{{ __('Export') }}</flux:button>
            </div>
        </div>
        <div class="flex justify-between items-center mb-4 gap-3">
            @can('update', $plan)
                <div class="flex items-center gap-2">
                    <select wire:model="targetPlanId" class="border rounded px-2 py-2">
                        <option value="">{{ __('Change selected users to...') }}</option>
                        @foreach($otherPlans as $p)
                            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="includeAllFiltered" />
                        <span>{{ __('Apply to all filtered') }}</span>
                    </label>
                    <flux:button wire:click="bulkChangeConfirm" variant="primary">{{ __('Apply') }}</flux:button>
                </div>
            @endcan
        </div>
        @if($confirmingBulkChange)
            <div class="rounded border border-yellow-300 bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-600 p-3 flex items-center justify-between gap-3">
                <div class="text-sm">{{ __('Are you sure you want to change plan for the selected users?') }}</div>
                <div class="flex items-center gap-2">
                    <flux:button wire:click="bulkChangeExecute" variant="primary">{{ __('Confirm') }}</flux:button>
                    <flux:button wire:click="$set('confirmingBulkChange', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                </div>
            </div>
        @endif

        <div class="relative overflow-x-auto ">
            <x-flowbite.table>
                <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <x-flowbite.table.head.row>
                        <x-flowbite.table.head.cell class="w-12">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" wire:click="toggleSelectAllPage" @checked($selectAllPage) />
                                <span class="text-xs">{{ __('All') }}</span>
                            </label>
                        </x-flowbite.table.head.cell>
                        <x-flowbite.table.head.cell>{{ __('ID') }}</x-flowbite.table.head.cell>
                        <x-flowbite.table.head.cell>{{ __('Name') }}</x-flowbite.table.head.cell>
                        <x-flowbite.table.head.cell>{{ __('Email') }}</x-flowbite.table.head.cell>
                        <x-flowbite.table.head.cell>{{ __('Subscription status') }}</x-flowbite.table.head.cell>
                        <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                    </x-flowbite.table.head.row>
                </x-flowbite.table.head>

                <x-flowbite.table.body>
                    @forelse ($users as $user)
                        <x-flowbite.table.body.row>
                            <x-flowbite.table.body.cell>
                                <input type="checkbox" wire:model="selected.{{ $user->id }}" />
                            </x-flowbite.table.body.cell>
                            <x-flowbite.table.body.cell>{{ $user->id }}</x-flowbite.table.body.cell>
                            <x-flowbite.table.body.cell>{{ $user->name }}</x-flowbite.table.body.cell>
                            <x-flowbite.table.body.cell>{{ $user->email }}</x-flowbite.table.body.cell>
                            <x-flowbite.table.body.cell>{{ $user->subscription?->status ?? '' }}</x-flowbite.table.body.cell>
                            <x-flowbite.table.body.cell class="text-right space-x-2">
                                <flux:button icon="eye" tag="a" href="{{ route('users.show', $user) }}" variant="ghost" />
                            </x-flowbite.table.body.cell>
                        </x-flowbite.table.body.row>
                    @empty
                        <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                            <x-flowbite.table.body.cell class="w-4 p-4" colspan="6">{{ __('No users found.') }}</x-flowbite.table.body.cell>
                        </x-flowbite.table.body.row>
                    @endforelse
                </x-flowbite.table.body>
            </x-flowbite.table>
        </div>

{{--        <div>--}}
{{--            {{ $users->links() }}--}}
{{--        </div>--}}
    </div>
</div>
