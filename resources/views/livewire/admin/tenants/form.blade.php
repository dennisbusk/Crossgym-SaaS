<div class="space-y-6 max-w-xl">
    <flux:heading size="xl">{{ $tenant?->exists ? __('Edit Tenant') : __('Create Tenant') }}</flux:heading>

    <x-banners />

    <form wire:submit="save" class="space-y-6">
        <flux:input label="{{ __('Name') }}" wire:model="name" />

        <flux:input label="{{ __('Domain') }}" wire:model="domain" placeholder="e.g. gym1.localhost" />

        <hr class="border-gray-200 dark:border-gray-700">

        <flux:heading size="lg">{{ __('PWA Settings') }}</flux:heading>

        <flux:input label="{{ __('App Name (PWA)') }}" wire:model="app_name" placeholder="{{ __('Display name on home screen') }}" />

        <div class="space-y-2">
            <flux:label>{{ __('App Icon') }}</flux:label>
            @if ($tenant?->icon_path)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $tenant->icon_path) }}" alt="Current Icon" class="w-16 h-16 rounded shadow">
                </div>
            @endif
            <input type="file" wire:model="icon" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            <flux:error name="icon" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:input type="color" label="{{ __('Theme Color') }}" wire:model="theme_color" />
            <flux:input type="color" label="{{ __('Background Color') }}" wire:model="background_color" />
        </div>

        <flux:textarea label="{{ __('Terms & Conditions') }}" wire:model="terms" rows="10" />

        <flux:field>
            <flux:label>{{ __('Allow members to manage subscriptions') }}</flux:label>
            <flux:description>{{ __('If disabled, only administrators can change member subscriptions.') }}</flux:description>
            <flux:switch wire:model="allow_member_billing_management" />
        </flux:field>

        <div class="flex gap-2 justify-end">
            <flux:button href="{{ route('tenants.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary">
                {{ $tenant?->exists ? __('Update') : __('Create') }}
            </flux:button>
        </div>
    </form>
</div>
