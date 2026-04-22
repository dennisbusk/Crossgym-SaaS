<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Bookings')" :subheading="__('View your upcoming and past bookings')">
        <div class="space-y-8">
            <x-banners/>
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">{{ __('Upcoming bookings') }}</h2>
                </div>
                <div class="relative overflow-x-auto">
                    <x-flowbite.table>
                        <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <x-flowbite.table.head.row>
                                <x-flowbite.table.head.cell>{{ __('Class') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell>{{ __('Start') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell>{{ __('End') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                            </x-flowbite.table.head.row>
                        </x-flowbite.table.head>

                        <x-flowbite.table.body>
                            @forelse($upcoming as $row)
                                <x-flowbite.table.body.row>
                                    <x-flowbite.table.body.cell>{{ $row['name'] }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell>{{ \Illuminate\Support\Carbon::parse($row['start'])->format('Y-m-d H:i') }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell>{{ \Illuminate\Support\Carbon::parse($row['end'])->format('Y-m-d H:i') }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell class="text-right">
                                        <flux:button icon="eye" variant="ghost" wire:click="showBooking({{ $row['id'] }})" />
                                    </x-flowbite.table.body.cell>
                                </x-flowbite.table.body.row>
                            @empty
                                <x-flowbite.table.body.row>
                                    <x-flowbite.table.body.cell colspan="4">{{ __('No upcoming bookings.') }}</x-flowbite.table.body.cell>
                                </x-flowbite.table.body.row>
                            @endforelse
                        </x-flowbite.table.body>
                    </x-flowbite.table>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">{{ __('Past bookings') }}</h2>
                </div>
                <div class="relative overflow-x-auto">
                    <x-flowbite.table>
                        <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <x-flowbite.table.head.row>
                                <x-flowbite.table.head.cell>{{ __('Class') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell>{{ __('Start') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell>{{ __('End') }}</x-flowbite.table.head.cell>
                                <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
                            </x-flowbite.table.head.row>
                        </x-flowbite.table.head>

                        <x-flowbite.table.body>
                            @forelse($past as $row)
                                <x-flowbite.table.body.row>
                                    <x-flowbite.table.body.cell>{{ $row['name'] }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell>{{ \Illuminate\Support\Carbon::parse($row['start'])->format('Y-m-d H:i') }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell>{{ \Illuminate\Support\Carbon::parse($row['end'])->format('Y-m-d H:i') }}</x-flowbite.table.body.cell>
                                    <x-flowbite.table.body.cell class="text-right">
                                        <flux:button icon="eye" variant="ghost" wire:click="showBooking({{ $row['id'] }})" />
                                    </x-flowbite.table.body.cell>
                                </x-flowbite.table.body.row>
                            @empty
                                <x-flowbite.table.body.row>
                                    <x-flowbite.table.body.cell colspan="4">{{ __('No past bookings.') }}</x-flowbite.table.body.cell>
                                </x-flowbite.table.body.row>
                            @endforelse
                        </x-flowbite.table.body>
                    </x-flowbite.table>
                </div>
            </div>
        </div>
    </x-settings.layout>

{{-- Show booking modal (mirrors calendar modal) --}}
<div x-data="{ open: @entangle('showModal') }">
    <div x-show="open"
         style="background-color: rgba(0,0,0,0.5);"
         class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 p-6 rounded shadow-lg w-xl">
            <h2 class="text-lg font-bold mb-2">{{ $selected['title'] ?? '' }}</h2>
            <p><strong>{{ __('Start') }}:</strong>
                <span>
                    @if(!empty($selected['start']))
                        {{ \Illuminate\Support\Carbon::parse($selected['start'])->format('Y-m-d H:i') }}
                    @endif
                </span>
            </p>
            <p><strong>{{ __('End') }}:</strong>
                <span>
                    @if(!empty($selected['end']))
                        {{ \Illuminate\Support\Carbon::parse($selected['end'])->format('Y-m-d H:i') }}
                    @endif
                </span>
            </p>
            <p><strong>{{ __('Trainer') }}:</strong> <span>{{ $selected['trainer'] ?? '' }}</span></p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                <span>
                    {{ ($selected['participantsCount'] ?? 0) }} / {{ ($selected['maxParticipants'] ?? 0) }}
                </span>
                {{ __('participants') }}
            </p>

            <div class="mt-2 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                @if(($selected['weeklyLimit'] ?? 0) > 0)
                    <p>
                        {{ __('Weekly bookings used') }}:
                        <span>{{ $selected['usedThisWeek'] ?? 0 }} / {{ $selected['weeklyLimit'] ?? 0 }}</span>
                    </p>
                @endif
                @if(!is_null($selected['creditsRemaining'] ?? null))
                    <p>
                        {{ __('Credits remaining') }}:
                        <span>{{ $selected['creditsRemaining'] }}</span>
                    </p>
                @endif
            </div>

            <div class="mt-4 flex items-center gap-2 justify-end">
                @if(!($selected['isPast'] ?? false))
                    <button
                        type="button"
                        wire:click="cancelBooking({{ $selected['id'] ?? 0 }})"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        {{ __('Cancel booking') }}
                    </button>
                @endif
                <button type="button" class="px-4 py-2 bg-blue-500 text-white rounded" @click="open = false; $wire.closeModal()">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>
</section>
