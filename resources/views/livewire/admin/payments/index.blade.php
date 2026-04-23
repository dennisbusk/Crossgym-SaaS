<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-semibold">{{ __('Payments') }}</h1>
            <flux:input wire:model.live="search" placeholder="{{ __('Search...') }}" icon="magnifying-glass" />
            <flux:select wire:model.live="status" placeholder="{{ __('All statuses') }}">
                <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                <flux:select.option value="succeeded">{{ __('Succeeded') }}</flux:select.option>
                <flux:select.option value="refunded">{{ __('Refunded') }}</flux:select.option>
                <flux:select.option value="partially_refunded">{{ __('Partially Refunded') }}</flux:select.option>
                <flux:select.option value="failed">{{ __('Failed') }}</flux:select.option>
            </flux:select>
        </div>
        <div class="flex items-center gap-2">
            <flux:button class="hover:cursor-pointer" wire:click="export" variant="ghost" icon="document-arrow-down" wire:loading.attr="disabled">
                {{ __('Export') }}
            </flux:button>
            <flux:button class="hover:cursor-pointer" wire:click="openManualPaymentModal" variant="ghost" icon="plus">
                {{ __('New Manual Payment') }}
            </flux:button>
        </div>
    </div>

    <x-banners/>

    <div class="relative overflow-x-auto ">

        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="id" :$sortField :$sortDirection>{{ __('ID') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell>{{ __('User') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.sortable field="amount" :$sortField :$sortDirection>{{ __('Amount') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="currency" :$sortField :$sortDirection>{{ __('Currency') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="status" :$sortField :$sortDirection>{{ __('Status') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="refunded_amount" :$sortField :$sortDirection>{{ __('Refunded') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="type" :$sortField :$sortDirection>{{ __('Type') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell>{{ __('Intent') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>

            <x-flowbite.table.body>
                @forelse ($payments as $payment)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $payment->id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $payment->user?->name }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            @php $amt = is_null($payment->amount) ? null : number_format(((int) $payment->amount) / 100, 2, ',', '.'); @endphp
                            {{ $amt ? $amt . ' ' . strtoupper((string) $payment->currency) : __('N/A') }}
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ strtoupper((string) $payment->currency) }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $payment->status }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            {{ number_format($payment->refunded_amount / 100, 2, ',', '.') }} {{ $payment->currency }}
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $payment->type }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="font-mono text-xs">{{ $payment->stripe_payment_intent_id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            @if($payment->status === 'succeeded' || $payment->status === 'partially_refunded')
                                <flux:button icon="arrow-path" wire:click="refund({{ $payment->id }})" variant="ghost" :title="__('Full Refund')" />
                            @endif
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                        <x-flowbite.table.body.cell class="w-4 p-4" colspan="8">{{ __('No payments found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div>
        {{ $payments->links() }}
    </div>

    <flux:modal wire:model="showManualPaymentModal" class="md:w-[20rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('New Manual Payment') }}</flux:heading>
            </div>

            <flux:select wire:model="manualUserId" label="{{ __('User') }}" filterable>
                @foreach($users as $user)
                    <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="manualAmount" type="number" step="0.01" label="{{ __('Amount') }} (DKK)" />

            <flux:textarea wire:model="manualNotes" label="{{ __('Notes') }}" />

            <div class="flex">
                <flux:spacer />
                <flux:button wire:click="$set('showManualPaymentModal', false)" variant="ghost" class="mr-2">{{ __('Cancel') }}</flux:button>
                <flux:button wire:click="saveManualPayment" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
