<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Billing')" :subheading="__('View your payment history and upcoming payment')">
        <div class="space-y-6">
            @if(session('error'))
                <x-banners type="danger">{{ session('error') }}</x-banners>
            @endif

            <div>
                <h2 class="text-xl font-semibold mb-4">{{ __('Available Subscriptions') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($plans as $plan)
                        <div class="card p-4 border rounded-xl flex flex-col justify-between bg-white dark:bg-gray-800 dark:border-gray-700">
                            <div>
                                <h3 class="font-bold text-lg">{{ $plan->name }}</h3>
                                <p class="text-2xl font-bold mt-2">{{ number_format($plan->amount, 2) }} {{ strtoupper($plan->currency) }}</p>
                                <p class="text-sm text-gray-500">{{ $plan->description }}</p>
                            </div>
                            <div class="mt-4">
                                @if($currentPlanId === $plan->stripe_price_id)
                                    <flux:button class="w-full" disabled variant="secondary">{{ __('Your current choice') }}</flux:button>
                                @else
                                    @if(tenant()->allow_member_billing_management)
                                        <flux:button class="w-full" wire:click="subscribe({{ $plan->id }})" variant="primary">{{ __('Select') }}</flux:button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-4 rounded border bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                <div class="font-semibold mb-2">{{ __('Upcoming payment') }}</div>
                @if($upcoming)
                    <div class="text-sm">
                        <span class="font-medium">{{ __('Amount') }}:</span>
                        <span>{{ number_format($upcoming['amount_due'], 2) }} {{ $upcoming['currency'] }}</span>
                        <span class="ml-3 font-medium">{{ __('Date') }}:</span>
                        <span>{{ $upcoming['next_payment_attempt'] ?? __('N/A') }}</span>
                    </div>
                @else
                    <div class="text-sm text-neutral-500">{{ __('No upcoming payment found.') }}</div>
                @endif
            </div>

            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">{{ __('Payment history') }}</h2>
                    {{-- Export placeholder for future extension --}}
                    {{-- <flux:button wire:click="export" variant="primary">{{ __('Export') }}</flux:button> --}}
                </div>

                <div class="relative overflow-x-auto">
                    <x-flowbite.table>
                        <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <x-flowbite.table.head.row>
                                <x-flowbite.table.head.cell>{{ __('Invoice') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell>{{ __('Date') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell>{{ __('Amount') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell>{{ __('Status') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                            </x-flowbite.table.head.row>
                        </x-flowbite.table.head>

                        <x-flowbite.table.body>
                            @forelse($invoices as $inv)
                                <x-flowbite.table.body.row>
                                    <x-flowbite.table.body.cell>{{ $inv['number'] ?? $inv['id'] }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell>{{ $inv['created'] }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell>{{ number_format($inv['amount_paid'], 2) }} {{ $inv['currency'] }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell>{{ __($inv['status']) }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell class="text-right">
                                        @if($inv['pdf'])
                                            <flux:button icon="arrow-down-tray" tag="a" href="{{ $inv['pdf'] }}" target="_blank" variant="ghost" />
                                        @endif
                                    </x-flowbite.table.body.cell>
                                </x-flowbite.table.body.row>
                            @empty
                                <x-flowbite.table.body.row>
                                    <x-flowbite.table.body.cell colspan="5">{{ __('No payments found.') }}</x-flowbite.table.body.cell>
                                </x-flowbite.table.body.row>
                            @endforelse
                        </x-flowbite.table.body>
                    </x-flowbite.table>
                </div>
            </div>
        </div>
    </x-settings.layout>
</section>
