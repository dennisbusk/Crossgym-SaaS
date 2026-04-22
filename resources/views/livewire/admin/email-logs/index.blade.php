<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-semibold">{{ __('Email Logs') }}</h1>
            <flux:input wire:model.live="search" placeholder="{{ __('Search...') }}" icon="magnifying-glass" />
        </div>
    </div>

    <x-banners/>

    <flux:card>
        <x-flowbite.table>
            <x-flowbite.table.head>
                <x-flowbite.table.head.row>
                    <x-flowbite.table.head.sortable field="sent_at" :$sortField :$sortDirection>{{ __('Sent At') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="to" :$sortField :$sortDirection>{{ __('Recipient') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="subject" :$sortField :$sortDirection>{{ __('Subject') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.sortable field="type" :$sortField :$sortDirection>{{ __('Type') }}</x-flowbite.table.head.sortable>
                    <x-flowbite.table.head.cell>{{ __('Status') }}</x-flowbite.table.head.cell>
                </x-flowbite.table.head.row>
            </x-flowbite.table.head>
            <x-flowbite.table.body>
                @forelse($logs as $log)
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell>{{ $log->sent_at->format('d/m/Y H:i') }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $log->user?->name ?? __('Unknown') }}</span>
                                <span class="text-xs text-gray-500">{{ $log->to }}</span>
                            </div>
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>{{ $log->subject }}</x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-800">{{ $log->type }}</span>
                        </x-flowbite.table.body.cell>
                        <x-flowbite.table.body.cell>
                            <div class="flex gap-2">
                                <span class="px-2 py-0.5 text-[10px] rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                                    {{ __('Sent') }}
                                </span>
                                @if($log->opened_at)
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100" title="{{ $log->opened_at->format('d/m/Y H:i') }}">
                                        {{ __('Opened') }}
                                    </span>
                                @endif
                            </div>
                        </x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @empty
                    <x-flowbite.table.body.row>
                        <x-flowbite.table.body.cell colspan="5">{{ __('Ingen logs fundet.') }}</x-flowbite.table.body.cell>
                    </x-flowbite.table.body.row>
                @endforelse
            </x-flowbite.table.body>
        </x-flowbite.table>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </flux:card>
</div>
