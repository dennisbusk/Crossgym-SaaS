<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('profile.settings')" :current="request()->routeIs('profile.settings')" wire:navigate>{{ __('Settings') }}</flux:navlist.item>
            <flux:navlist.item :href="route('profile.password')" :current="request()->routeIs('profile.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <flux:navlist.item :href="route('two-factor.show')" :current="request()->routeIs('two-factor.show')" wire:navigate>{{ __('Two-Factor Auth') }}</flux:navlist.item>
            @endif
            <flux:navlist.item :href="route('profile.bookings')" :current="request()->routeIs('profile.bookings')" wire:navigate>{{ __('Bookings') }}</flux:navlist.item>
            <flux:navlist.item :href="route('profile.billing')" :current="request()->routeIs('profile.billing')"  wire:navigate>{{ __('Billing') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
