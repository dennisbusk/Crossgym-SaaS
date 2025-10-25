    <div class="space-y-6">
{{--    <div>--}}
{{--        <flux:fieldset>--}}
{{--            <flux:legend>{{ __('Stripe') }}</flux:legend>--}}
{{--            <flux:input--}}
{{--                label="{{ __('Stripe Key') }}"--}}
{{--                wire:model.defer="stripe_key"--}}
{{--                placeholder="pk_live_..."--}}
{{--            />--}}
{{--            <flux:input--}}
{{--                label="{{ __('Stripe Secret') }}"--}}
{{--                type="password"--}}
{{--                wire:model.defer="stripe_secret"--}}
{{--                placeholder="sk_live_..."--}}
{{--            />--}}
{{--            <flux:input--}}
{{--                label="{{ __('Stripe Webhook Secret') }}"--}}
{{--                type="password"--}}
{{--                wire:model.defer="stripe_webhook_secret"--}}
{{--                placeholder="whsec_..."--}}
{{--            />--}}
{{--        </flux:fieldset>--}}
{{--    </div>--}}

    <div class="mt-6 flex justify-end gap-2">
        <flux:link :href="route('superadmin.dashboard')" variant="ghost">{{ __('Cancel') }}</flux:link>
        <flux:button wire:click="save" variant="primary">{{ __('Save') }}</flux:button>
    </div>
    </div>
