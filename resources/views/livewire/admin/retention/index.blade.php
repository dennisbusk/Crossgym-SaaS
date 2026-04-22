<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Retention - Kom Tilbage') }}</h1>
    </div>

    <x-banners/>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Inaktive medlemmer (over 14 dage)') }}</flux:heading>

                <x-flowbite.table>
                    <x-flowbite.table.head>
                        <x-flowbite.table.head.row>
                            <x-flowbite.table.head.sortable field="name" :$sortField :$sortDirection>{{ __('Name') }}</x-flowbite.table.head.sortable>
                            <x-flowbite.table.head.sortable field="last_check_in_at" :$sortField :$sortDirection>{{ __('Last Check-in') }}</x-flowbite.table.head.sortable>
                            <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                        </x-flowbite.table.head.row>
                    </x-flowbite.table.head>
                    <x-flowbite.table.body>
                        @forelse($inactiveUsers as $user)
                            <x-flowbite.table.body.row>
                                <x-flowbite.table.body.cell>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $user->email }}</span>
                                    </div>
                                </x-flowbite.table.body.cell>
                                <x-flowbite.table.body.cell>
                                    @php
                                        $days = $user->last_check_in_at ? now()->diffInDays($user->last_check_in_at) : now()->diffInDays($user->created_at);
                                        $color = $days > 30 ? 'text-red-600' : ($days > 21 ? 'text-orange-500' : 'text-yellow-500');
                                    @endphp
                                    <span class="{{ $color }} font-bold">
                                        {{ $user->last_check_in_at ? $user->last_check_in_at->diffForHumans() : __('Never') }}
                                        ({{ $days }} {{ __('dage') }})
                                    </span>
                                </x-flowbite.table.body.cell>
                                <x-flowbite.table.body.cell class="text-right">
                                    <flux:button wire:click="sendRecallEmail({{ $user->id }})" icon="envelope" variant="ghost">
                                        {{ __('Send mail') }}
                                    </flux:button>
                                </x-flowbite.table.body.cell>
                            </x-flowbite.table.body.row>
                        @empty
                            <x-flowbite.table.body.row>
                                <x-flowbite.table.body.cell colspan="3">{{ __('Ingen inaktive medlemmer fundet.') }}</x-flowbite.table.body.cell>
                            </x-flowbite.table.body.row>
                        @endforelse
                    </x-flowbite.table.body>
                </x-flowbite.table>

                <div class="mt-4">
                    {{ $inactiveUsers->links() }}
                </div>
            </flux:card>
        </div>

        <div class="space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Recent follow-ups') }}</flux:heading>
                <div class="space-y-4">
                    @forelse($emailLogs as $log)
                        <div class="flex items-start gap-3 p-3 border rounded-lg dark:border-gray-700">
                            <flux:icon icon="envelope" class="mt-1" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $log->user?->name ?? $log->to }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $log->sent_at->diffForHumans() }}
                                </p>
                                <div class="mt-1 flex gap-2">
                                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                                        {{ __('Sent') }}
                                    </span>
                                    @if($log->opened_at)
                                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                            {{ __('Opened') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No history yet.') }}</p>
                    @endforelse
                </div>
            </flux:card>
        </div>
    </div>
</div>
