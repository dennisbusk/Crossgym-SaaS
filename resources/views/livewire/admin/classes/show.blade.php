<div class="space-y-6 max-w-7xl">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">{{ __('Class') }}: {{ $gymClass->getTranslation('name', app()->getLocale()) }}</h1>
        <div class="flex gap-2">
            <flux:button tag="a" href="{{ route('classes.index') }}" variant="ghost">{{ __('Back') }}</flux:button>
            <flux:button tag="a" href="{{ route('classes.edit', $gymClass) }}" variant="primary">{{ __('Edit') }}</flux:button>
        </div>
    </div>

    <x-banners/>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-1 space-y-6">
            <div class="rounded-xl border p-6 bg-white shadow-sm space-y-4">
                <h2 class="text-lg font-semibold border-b pb-2">{{ __('Class Details') }}</h2>
                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Type') }}</div>
                        <div class="font-medium">{{ $gymClass->classType?->getTranslation('name', app()->getLocale()) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Trainer') }}</div>
                        <div class="font-medium">{{ $gymClass->trainer?->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Start') }}</div>
                        <div class="font-medium">{{ optional($gymClass->class_start)->format('Y-m-d H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('End') }}</div>
                        <div class="font-medium">{{ optional($gymClass->class_end)->format('Y-m-d H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Capacity') }}</div>
                        <div class="font-medium">{{ $gymClass->participants->count() + $gymClass->trials->count() }} / {{ $gymClass->max_participants ?? '∞' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border p-6 bg-white shadow-sm space-y-4">
                <h2 class="text-lg font-semibold border-b pb-2">{{ __('Add Participant') }}</h2>
                <div class="space-y-4">
                    <div class="relative">
                        <flux:input
                            label="{{ __('Search user') }}"
                            placeholder="{{ __('Name or email...') }}"
                            wire:model.live="userSearch"
                        />

                        @if(!empty($userSearch) && $users->isNotEmpty())
                            <div class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-auto">
                                @foreach($users as $user)
                                    <button
                                        type="button"
                                        wire:click="selectUser({{ $user->id }})"
                                        class="w-full text-left px-4 py-2 hover:bg-gray-100 focus:outline-none"
                                    >
                                        <div class="font-medium text-sm">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <flux:button
                        class="w-full"
                        variant="primary"
                        wire:click="addParticipant"
                        :disabled="!$selectedUserId"
                    >
                        {{ __('Add to class') }}
                    </flux:button>
                </div>
            </div>

            <div class="rounded-xl border p-6 bg-white shadow-sm space-y-4">
                <h2 class="text-lg font-semibold border-b pb-2">{{ __('Add Trial Booking') }}</h2>
                <div class="space-y-4">
                    <flux:input
                        label="{{ __('Name') }}"
                        placeholder="{{ __('Trial participant name') }}"
                        wire:model="trialName"
                        wire:keydown.enter="addTrial"
                    />
                    <flux:button class="w-full" variant="outline" wire:click="addTrial">
                        {{ __('Add Trial') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 space-y-6">
            <div class="rounded-xl border bg-white shadow-sm overflow-hidden">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-semibold">{{ __('Attendees') }}</h2>
                    <span class="text-sm font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                        {{ $gymClass->participants->count() + $gymClass->trials->count() }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-3">{{ __('Name') }}</th>
                                <th class="px-6 py-3">{{ __('Type') }}</th>
                                <th class="px-6 py-3">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            {{-- Regular Participants --}}
                            @foreach ($gymClass->participants as $participant)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                                {{ $participant->initials() }}
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $participant->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $participant->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                            {{ __('Member') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($participant->pivot->checked_at)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                                <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true"><circle cx="3" cy="3" r="3" /></svg>
                                                {{ __('Checked in') }} ({{ \Carbon\Carbon::parse($participant->pivot->checked_at)->format('H:i') }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">
                                                <svg class="h-1.5 w-1.5 fill-yellow-500" viewBox="0 0 6 6" aria-hidden="true"><circle cx="3" cy="3" r="3" /></svg>
                                                {{ __('Booked') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        @if (!$participant->pivot->checked_at)
                                            <flux:button icon="check" variant="ghost" wire:click="checkInParticipant({{ $participant->id }})" />
                                        @endif
                                        <flux:button icon="trash" variant="ghost" wire:click="removeParticipant({{ $participant->id }})" />
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Trial Bookings --}}
                            @foreach ($gymClass->trials as $trial)
                                <tr class="hover:bg-gray-50 transition-colors bg-purple-50/30">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-purple-200 flex items-center justify-center text-xs font-bold text-purple-600">
                                                <flux:icon.user class="w-4 h-4" />
                                            </div>
                                            <div class="font-medium text-gray-900">{{ $trial->name }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-medium text-purple-700">
                                            {{ __('Trial') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($trial->checked_at)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                                <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true"><circle cx="3" cy="3" r="3" /></svg>
                                                {{ __('Checked in') }} ({{ \Carbon\Carbon::parse($trial->checked_at)->format('H:i') }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">
                                                <svg class="h-1.5 w-1.5 fill-yellow-500" viewBox="0 0 6 6" aria-hidden="true"><circle cx="3" cy="3" r="3" /></svg>
                                                {{ __('Booked') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        @if (!$trial->checked_at)
                                            <flux:button icon="check" variant="ghost" wire:click="checkInTrial({{ $trial->id }})" />
                                        @endif
                                        <flux:button icon="trash" variant="ghost" wire:click="removeTrial({{ $trial->id }})" />
                                    </td>
                                </tr>
                            @endforeach

                            @if ($gymClass->participants->isEmpty() && $gymClass->trials->isEmpty())
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">
                                        {{ __('No participants or trials yet.') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
