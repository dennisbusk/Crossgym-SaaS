<div class="mx-auto max-w-4xl p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ __('View Tenant') }}</h1>
        <div class="space-x-2">
            <flux:button tag="a" href="{{ route('tenants.index') }}" variant="ghost">{{ __('Back') }}</flux:button>
            <flux:button tag="a" href="{{ route('tenants.edit', $tenant) }}" variant="ghost">{{ __('Edit') }}</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card space-y-3">
            <div>
                <div class="text-sm text-gray-500">{{ __('ID') }}</div>
                <div class="font-medium">{{ $tenant->id }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Name') }}</div>
                <div class="font-medium">{{ $tenant->name }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Domain') }}</div>
                <div class="font-medium">{{ $tenant->domain }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Created') }}</div>
                <div class="font-medium">{{ optional($tenant->created_at)->format('Y-m-d H:i') }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Updated') }}</div>
                <div class="font-medium">{{ optional($tenant->updated_at)->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <div class="card space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">{{ __('Subscription') }}</div>
                <flux:button tag="a" href="{{ route('tenant.subscription') }}" variant="ghost">{{ __('Change subscription') }}</flux:button>
            </div>
            <div class="font-medium">
                @if($tenant->subscriptionOption)
                    {{ $tenant->subscriptionOption->name }}
                    <div class="text-sm text-gray-500">{{ __('Type') }}: {{ __($tenant->subscriptionOption->type === 'transaction_fee' ? 'Transaction fee' : 'Member fee') }}, {{ __('Value') }}: {{ rtrim(rtrim(number_format((float)$tenant->subscriptionOption->value, 3, ',', ''), '0'), ',') }}{{ $tenant->subscriptionOption->type === 'transaction_fee' ? '%' : ' ' . __('DKK per member') }}</div>
                @else
                    <span class="text-gray-500">{{ __('N/A') }}</span>
                @endif
            </div>

            <div class="pt-2 border-t mt-2">
                <div class="text-sm text-gray-500 mb-1">{{ __('Stripe Connect') }}</div>
                <ul class="text-sm space-y-1">
                    <li>{{ __('Onboarded') }}: <span class="font-medium">{{ $tenant->stripe_connect_onboarded ? __('Yes') : __('No') }}</span></li>
                    <li>{{ __('Charges enabled') }}: <span class="font-medium">{{ $tenant->stripe_connect_charges_enabled ? __('Yes') : __('No') }}</span></li>
                    <li>{{ __('Payouts enabled') }}: <span class="font-medium">{{ $tenant->stripe_connect_payouts_enabled ? __('Yes') : __('No') }}</span></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="rounded border px-4 py-3 space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ __('Year-to-date metrics') }}</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <div class="text-sm text-gray-500">{{ __('Transactions (YTD)') }}</div>
                <div class="font-medium">{{ $transactionsCountYtd }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Tenant earned (YTD)') }}</div>
                <div class="font-medium">{{ number_format(($tenantEarningsYtdMinor ?? 0)/100, 2, ',', '.') }} {{ __('DKK') }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Platform earned (YTD)') }}</div>
                <div class="font-medium">
                    @if(!is_null($platformEarningsYtdMinor))
                        {{ number_format($platformEarningsYtdMinor/100, 2, ',', '.') }} {{ __('DKK') }}
                    @else
                        <span class="text-gray-500">{{ __('N/A') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
