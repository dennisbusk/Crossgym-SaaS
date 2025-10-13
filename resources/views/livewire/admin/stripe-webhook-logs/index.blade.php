<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Stripe Webhook Logs') }}</h1>
        <div class="flex items-center gap-2">
            <flux:input placeholder="{{ __('Search...') }}" wire:model.live="search" />
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.cell>{{ __('ID') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Event Type') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Status') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Processed At') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell>{{ __('Error') }}</x-flowbite.table.head.cell>
                    <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>

            <x-flowbite.table.body>
                @forelse ($logs as $log)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $log->id }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $log->event_type }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $log->status }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ optional($log->processed_at)->format('Y-m-d H:i') }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="truncate max-w-[300px]">{{ $log->error }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell class="text-right space-x-2">
                            {{-- Reserved for future actions --}}
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                        <x-flowbite.table.body.cell class="w-4 p-4" colspan="6">{{ __('No webhook logs found.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>
    </div>

    <div>
        {{ $logs->links() }}
    </div>
</div>
