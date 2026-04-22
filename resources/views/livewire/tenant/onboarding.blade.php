<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="flex justify-self-start">
            <h1 class="text-2xl font-semibold">{{ __('Onboarding') }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <div class="text-sm text-neutral-500">{{ __('Step') }} {{ $step }} {{ __('of') }} 4</div>
            <div class="w-48 h-2 bg-neutral-200 dark:bg-neutral-800 rounded">
                <div class="h-2 bg-blue-600 rounded" style="width: {{ ($step/4)*100 }}%"></div>
            </div>
        </div>
    </div>

    <x-banners/>

    <div class="rounded border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6">
        @if ($step === 1)
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">{{ __('Welcome to CrossGym SaaS') }}</h2>
                <p class="text-neutral-600 dark:text-neutral-400">{{ __('This short guide will help you finish the initial setup for your gym.') }}</p>
            </div>
        @elseif ($step === 2)
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">{{ __('Basic settings') }}</h2>
                <p class="text-neutral-600 dark:text-neutral-400">{{ __('Configure your gym\'s basic settings.') }}</p>

                <div class="space-y-4 max-w-md">
                    <flux:input label="{{ __('App Name') }}" wire:model="app_name" />
                    <flux:input label="{{ __('Theme Color') }}" type="color" wire:model="theme_color" />

                    <flux:field>
                        <flux:label>{{ __('Allow members to manage subscriptions') }}</flux:label>
                        <flux:switch wire:model="allow_member_billing_management" />
                    </flux:field>
                </div>
            </div>
        @elseif ($step === 3)
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">{{ __('Choose subscription plan') }}</h2>
                <p class="text-neutral-600 dark:text-neutral-400">{{ __('Select the pricing model that suits your business best.') }}</p>
                @livewire('admin.tenant-choose-subscription')
            </div>
        @elseif ($step === 4)
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">{{ __('Confirmation') }}</h2>
                <p class="text-neutral-600 dark:text-neutral-400">{{ __('Review your selections and complete onboarding.') }}</p>
                <div class="p-4 rounded bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200">
                    {{ __('You\'re all set! You can always change settings later.') }}
                </div>
            </div>
        @endif

        <div class="mt-6 flex justify-end gap-2">
            @if ($step > 1)
                <flux:button wire:click="prev" variant="ghost">{{ __('Back') }}</flux:button>
            @endif
            @if ($step < 4)
                <flux:button wire:click="next" variant="primary">{{ __('Next') }}</flux:button>
            @else
                <flux:button wire:click="complete" variant="primary">{{ __('Finish') }}</flux:button>
            @endif
        </div>
    </div>
</div>
