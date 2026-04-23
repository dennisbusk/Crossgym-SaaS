<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('Plans') }}</h1>
        </div>
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <flux:button class="hover:cursor-pointer" wire:click="export" variant="ghost" icon="document-arrow-down" wire:loading.attr="disabled">
                    {{ __('Export') }}
                </flux:button>
                @can('create', \App\Models\Plan::class)
                    <flux:button tag="a" href="{{ route('plans.create') }}" variant="ghost" icon="plus">
                        {{ __('Create Plan') }}
                    </flux:button>
                @endcan
            </div>
        </div>
    </div>

    <x-banners/>

    <div class="relative overflow-x-auto ">

        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="id" :$sortField :$sortDirection>{{ __('ID') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="name" :$sortField :$sortDirection>{{ __('Name') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="amount" :$sortField :$sortDirection>{{ __('Price') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="interval" :$sortField :$sortDirection>{{ __('Interval') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="stripe_price_id" :$sortField :$sortDirection>{{ __('Stripe Price ID') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="subscribers_count" :$sortField :$sortDirection>{{ __('Subscribers') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>

            <x-flowbite.table.body>
                @forelse ($plans as $plan)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $plan->id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $plan->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            @php
                                $amount = is_null($plan->amount) ? null : number_format(((int) $plan->amount) / 100, 2, ',', '.');
                            @endphp
                            {{ $amount ? $amount . ' ' . strtoupper((string) $plan->currency) : __('N/A') }}
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $plan->interval }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="font-mono text-xs">{{ $plan->stripe_price_id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $plan->subscribers_count ?? 0 }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right">
                            <div class="flex justify-end items-center gap-2">
                                <div class="hidden sm:flex items-center gap-2">
                                    @can('view', $plan)
                                        <flux:button icon="eye" tag="a" href="{{ route('plans.show', $plan) }}" variant="ghost" />
                                    @endcan
                                    @can('update', $plan)
                                        <flux:button icon="pencil-square" tag="a" href="{{ route('plans.edit', $plan) }}" variant="ghost" />
                                    @endcan
                                </div>

                                <flux:dropdown align="end" aria-label="{{ __('Actions') }}">
                                    <flux:button icon="ellipsis-horizontal" variant="ghost" />

                                    <flux:menu>
                                        <div class="sm:hidden">
                                            @can('view', $plan)
                                                <flux:menu.item icon="eye" tag="a" href="{{ route('plans.show', $plan) }}">{{ __('Show') }}</flux:menu.item>
                                            @endcan
                                            @can('update', $plan)
                                                <flux:menu.item icon="pencil-square" tag="a" href="{{ route('plans.edit', $plan) }}">{{ __('Edit') }}</flux:menu.item>
                                            @endcan
                                            <flux:menu.separator />
                                        </div>
                                        @can('delete', $plan)
                                            <flux:menu.item icon="trash" wire:click="delete({{ $plan->id }})" variant="danger">{{ __('Delete') }}</flux:menu.item>
                                        @endcan
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                        <x-flowbite.table.body.cell class="w-4 p-4" colspan="7">{{ __('No plans found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div>
        {{ $plans->links() }}
    </div>
</div>
