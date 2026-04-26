<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ $isEditing ? __('Rediger Skabelon') : __('Ny Skabelon') }}: {{ $template->name }}</h1>
        <flux:button tag="a" href="{{ route('admin.email-templates.index') }}" variant="ghost">{{ __('Tilbage') }}</flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-6">
            <div class="card bg-white p-6 rounded-xl border">
                <form wire:submit="save" class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('Subject') }}</flux:label>
                        <flux:input wire:model="template.subject" />
                        <flux:error name="template.subject" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Content') }}</flux:label>
                        <flux:textarea wire:model="template.content" rows="15" />
                        <flux:error name="template.content" />
                        <div class="mt-2 text-xs text-gray-500">
                            {{ __('Use merge fields like:') }} <code class="bg-gray-100 px-1 rounded">@{{ user_name }}</code>, <code class="bg-gray-100 px-1 rounded">@{{ tenant_name }}</code>, <code class="bg-gray-100 px-1 rounded">@{{ plan_name }}</code>
                        </div>
                    </flux:field>

                    <div class="flex justify-end space-x-2">
                        <flux:button wire:click="generatePreview" variant="filled">{{ __('Preview') }}</flux:button>
                        <flux:button type="submit" variant="primary">{{ __('Save Template') }}</flux:button>
                    </div>
                </form>
            </div>

            @if($previewHtml)
                <div class="card bg-white p-6 rounded-xl border">
                    <h3 class="text-lg font-medium mb-4">{{ __('Preview') }}</h3>
                    <div class="p-4 bg-gray-50 rounded border text-gray-800 whitespace-pre-wrap">
                        {!! $previewHtml !!}
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="card bg-white p-6 rounded-xl border">
                <h3 class="text-lg font-medium mb-4">{{ __('Send Test Email') }}</h3>
                <div class="space-y-4">
                    <flux:field>
                        <flux:label>{{ __('Recipient Email') }}</flux:label>
                        <flux:input wire:model="testEmail" type="email" />
                        <flux:error name="testEmail" />
                    </flux:field>

                    <flux:button wire:click="sendTestEmail" class="w-full" variant="filled">{{ __('Send Test') }}</flux:button>

                    @if (session('test-status'))
                        <div class="mt-2 text-sm text-green-600">
                            {{ session('test-status') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card bg-white p-6 rounded-xl border">
                <h3 class="text-lg font-medium mb-4">{{ __('Available Merge Fields') }}</h3>
                <ul class="text-sm space-y-2 text-gray-600">
                    <li><code class="bg-gray-100 px-1 rounded">@{{ user_name }}</code>: {{ __('User name') }}</li>
                    <li><code class="bg-gray-100 px-1 rounded">@{{ user_email }}</code>: {{ __('User email') }}</li>
                    <li><code class="bg-gray-100 px-1 rounded">@{{ tenant_name }}</code>: {{ __('Gym name') }}</li>
                    <li><code class="bg-gray-100 px-1 rounded">@{{ plan_name }}</code>: {{ __('Plan name') }}</li>
                    <li><code class="bg-gray-100 px-1 rounded">@{{ class_name }}</code>: {{ __('Class name') }}</li>
                    <li><code class="bg-gray-100 px-1 rounded">@{{ class_date }}</code>: {{ __('Class date and time') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
