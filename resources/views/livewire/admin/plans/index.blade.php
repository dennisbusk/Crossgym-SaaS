<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Plans') }}</h1>
    
    </div>
    
    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif
    
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <x-flowbite.button class="hover:cursor-pointer" wire:click="export" variant="primary">
                    {{ __('Export') }}
                </x-flowbite.button>
                <x-flowbite.button tag="a" href="{{ route('plans.create') }}" variant="primary">
                    {{ __('New Plan') }}
                </x-flowbite.button>
            </div>
        </div>
        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.cell>{{ __('ID') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Name') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Price') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Interval') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Stripe Price ID') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Subscribers') }}</x-flowbite.table.head.cell>
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
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            @if(\Illuminate\Support\Facades\Route::has('plans.show'))
                                <x-flowbite.button icon="eye" href="{{ route('plans.show', $plan) }}" variant="ghost" />
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('plans.edit'))
                                <x-flowbite.button icon="pencil-square" href="{{ route('plans.edit', $plan) }}" variant="ghost" />
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('plans.destroy'))
                                <x-flowbite.button icon="trash" wire:click="delete({{ $plan->id }})" variant="ghost" />
                            @endif
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
