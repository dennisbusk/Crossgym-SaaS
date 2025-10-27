<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('Payments') }}</h1>
        </div>
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <x-flowbite.button class="hover:cursor-pointer" wire:click="export" variant="ghost">
                    {{ __('Export') }}
                </x-flowbite.button>
            </div>
        </div>
    </div>
    
    <x-banners/>
    
    <div class="relative overflow-x-auto ">
        
        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.cell>{{ __('ID') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('User') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Amount') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Currency') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Status') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Type') }}</x-flowbite.table.head.cell>
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
                        <x-flowbite.table.body.cell>{{ $payment->type }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="font-mono text-xs">{{ $payment->stripe_payment_intent_id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            {{-- Reserved for future actions --}}
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
</div>
