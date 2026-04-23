<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <div class="w-full max-w-xl">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        @if(count($this->availableWidgets) > 0)
            <div class="mt-10 space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Dashboard Widgets') }}</flux:heading>
                    <flux:subheading>{{ __('Choose which widgets you want to see on your dashboard.') }}</flux:subheading>
                </div>

                <div class="space-y-4">
                    @foreach($this->availableWidgets as $key => $widget)
                        <flux:switch wire:model="dashboardSettings.{{ $key }}" :label="$widget['label']" />
                    @endforeach
                </div>

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" wire:click="updateProfileInformation">{{ __('Save Widgets') }}</flux:button>
                </div>
            </div>
        @endif

        <!-- Current plan/credits -->
        @php($sub = auth()->user()?->subscription)
        @php($plan = $sub?->stripe_price_id ? \App\Models\Plan::query()->where('stripe_price_id', $sub->stripe_price_id)->first() : null)
        @if($sub)
            <div class="mt-6 p-4 rounded border bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                <div class="font-semibold mb-2">{{ __('Current plan') }}</div>
                <div class="space-y-1 text-sm">
                    <div>
                        <span class="font-medium">{{ __('Plan') }}:</span>
                        <span>{{ $plan?->name ?? __('N/A') }}</span>
                        <span class="ml-2">(
                            {{ ($sub->plan_type === 'subscription') ? __('Subscription') : __('One-off') }}
                        )</span>
                    </div>
                    <div>
                        <span class="font-medium">{{ __('Status') }}:</span>
                        <span>{{ __($sub->status ?? 'N/A') }}</span>
                    </div>
                    @if(($sub->plan_type ?? 'subscription') === 'subscription')
                        @php($weekly = (int) (($plan?->metadata['weekly_booking_limit'] ?? 0)))
                        <div>
                            <span class="font-medium">{{ __('Weekly booking limit') }}:</span>
                            <span>{{ $weekly > 0 ? $weekly : __('None') }}</span>
                        </div>
                    @else
                        <div>
                            <span class="font-medium">{{ __('Credits remaining') }}:</span>
                            <span>{{ (int)($sub->credits_remaining ?? 0) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        <div class="mt-4">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
            </flux:radio.group>
        </div>
        <livewire:profile.delete-user-form />
        </div>
    </x-settings.layout>
</section>
