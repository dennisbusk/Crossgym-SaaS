<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold">{{ __('User Details') }}</h1>
        <div class="flex gap-2">
            @can('impersonate', $user)
                <flux:button wire:click="impersonate" variant="primary" icon="identification">{{ __('Impersonate') }}</flux:button>
            @endcan
            <flux:button wire:click="sendResetPassword" variant="outline" icon="envelope">{{ __('Send reset password mail') }}</flux:button>
            <flux:button tag="a" href="{{ route('users.edit', $user) }}" variant="outline" icon="pencil-square">{{ __('Edit') }}</flux:button>
            <flux:button tag="a" href="{{ route('users.index') }}" variant="ghost" icon="arrow-left">{{ __('Back to list') }}</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-xl border p-6 space-y-4 bg-white dark:bg-gray-800">
            <h2 class="text-lg font-medium border-b pb-2">{{ __('Information') }}</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500">{{ __('Name') }}</div>
                    <div class="font-medium">{{ $user->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('Email') }}</div>
                    <div class="font-medium">{{ $user->email }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('Role') }}</div>
                    <div class="font-medium">{{ $user->role?->name ?? __('N/A') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('Tenant') }}</div>
                    <div class="font-medium">{{ $user->tenant?->name ?? __('N/A') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('Member Number') }}</div>
                    <div class="font-medium">{{ $user->medlemsnummer ?? __('N/A') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">{{ __('Created At') }}</div>
                    <div class="font-medium">{{ $user->created_at->format('d.m.Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tidligere bookinger -->
        <div class="space-y-4">
            <h2 class="text-xl font-semibold">{{ __('Past bookings') }}</h2>
            <div class="relative overflow-x-auto overflow-y-visible">
                <x-flowbite.table :useDataTables="false">
                    <x-flowbite.table.head>
                        <x-flowbite.table.head.row>
                            <x-flowbite.table.head.cell>{{ __('Class') }}</x-flowbite.table.head.cell>
                            <x-flowbite.table.head.cell>{{ __('Date') }}</x-flowbite.table.head.cell>
                            <x-flowbite.table.head.cell>{{ __('Status') }}</x-flowbite.table.head.cell>
                        </x-flowbite.table.head.row>
                    </x-flowbite.table.head>
                    <x-flowbite.table.body>
                        @forelse ($pastBookings as $booking)
                            <x-flowbite.table.body.row>
                                <x-flowbite.table.body.cell>{{ $booking->name }}</x-flowbite.table.body.cell>
                                <x-flowbite.table.body.cell>{{ $booking->class_start->format('d.m.Y H:i') }}</x-flowbite.table.body.cell>
                                <x-flowbite.table.body.cell>
                                    @if($booking->pivot->check_in_id)
                                        <flux:badge color="green" size="sm" inset="top bottom">{{ __('Checked in') }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm" inset="top bottom">{{ __('Not checked in') }}</flux:badge>
                                    @endif
                                </x-flowbite.table.body.cell>
                            </x-flowbite.table.body.row>
                        @empty
                            <x-flowbite.table.body.row>
                                <x-flowbite.table.body.cell colspan="3">{{ __('No past bookings.') }}</x-flowbite.table.body.cell>
                            </x-flowbite.table.body.row>
                        @endforelse
                    </x-flowbite.table.body>
                </x-flowbite.table>
            </div>
        </div>

        <!-- Fremtidige bookinger -->
        <div class="space-y-4">
            <h2 class="text-xl font-semibold">{{ __('Upcoming bookings') }}</h2>
            <div class="relative overflow-x-auto overflow-y-visible">
                <x-flowbite.table :useDataTables="false">
                    <x-flowbite.table.head>
                        <x-flowbite.table.head.row>
                            <x-flowbite.table.head.cell>{{ __('Class') }}</x-flowbite.table.head.cell>
                            <x-flowbite.table.head.cell>{{ __('Date') }}</x-flowbite.table.head.cell>
                        </x-flowbite.table.head.row>
                    </x-flowbite.table.head>
                    <x-flowbite.table.body>
                        @forelse ($upcomingBookings as $booking)
                            <x-flowbite.table.body.row>
                                <x-flowbite.table.body.cell>{{ $booking->name }}</x-flowbite.table.body.cell>
                                <x-flowbite.table.body.cell>{{ $booking->class_start->format('d.m.Y H:i') }}</x-flowbite.table.body.cell>
                            </x-flowbite.table.body.row>
                        @empty
                            <x-flowbite.table.body.row>
                                <x-flowbite.table.body.cell colspan="2">{{ __('No upcoming bookings.') }}</x-flowbite.table.body.cell>
                            </x-flowbite.table.body.row>
                        @endforelse
                    </x-flowbite.table.body>
                </x-flowbite.table>
            </div>
        </div>
    </div>
</div>
