<flux:modal name="ai-coach-modal" focusable class="max-w-3xl" wire:close="resetForNew">
    <div class="space-y-6">
        <flux:heading size="lg">{{ __('Generate WOD with AI Coach') }}</flux:heading>

        @if(($this->step ?? 'parameters') === 'parameters')
            <form wire:submit="generate" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">{{ __('Intensity') }}</label>
                    <flux:radio.group wire:model="intensity" class="flex flex-wrap gap-2">
                        @foreach($this->settings->intensity ?? [] as $opt)
                            <flux:radio :value="$opt" :label="ucfirst($opt)" />
                        @endforeach
                    </flux:radio.group>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">{{ __('Equipment') }}</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->settings->equipment ?? [] as $opt)
                            <label class="inline-flex items-center gap-1 cursor-pointer">
                                <input type="checkbox" wire:model="equipment" value="{{ $opt }}" class="rounded border-gray-300">
                                <span>{{ ucfirst($opt) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('equipment')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">{{ __('Focus Area') }}</label>
                    <flux:radio.group wire:model="focus_area" class="flex flex-wrap gap-2">
                        @foreach($this->settings->focus_area ?? [] as $opt)
                            <flux:radio :value="$opt" :label="ucfirst($opt)" />
                        @endforeach
                    </flux:radio.group>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">{{ __('Difficulty') }}</label>
                    <flux:radio.group wire:model="difficulty" class="flex flex-wrap gap-2">
                        @foreach($this->settings->difficulty ?? [] as $opt)
                            <flux:radio :value="$opt" :label="ucfirst($opt)" />
                        @endforeach
                    </flux:radio.group>
                </div>

                <div>
                    <label class="block text-sm font-medium">{{ __('Duration (minutes)') }}</label>
                    <flux:input type="number" wire:model="duration" min="{{ $this->settings->duration_min ?? 5 }}" max="{{ $this->settings->duration_max ?? 90 }}" />
                </div>

                @if($this->error)
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-3 text-red-700 dark:text-red-400 text-sm">
                        {{ $this->error }}
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('Generate WOD') }}</span>
                        <span wire:loading>{{ __('Generating...') }}</span>
                    </flux:button>
                </div>
            </form>
        @else
            <div class="space-y-4">
                <div class="relative">
                    <div class="rounded-lg border p-4 min-h-[200px] bg-zinc-50 dark:bg-zinc-900 prose dark:prose-invert max-w-none">
                        @if($this->wodHtml)
                            {!! $this->wodHtml !!}
                        @endif
                    </div>

                    <div wire:loading wire:target="refine" class="absolute inset-0 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-sm flex items-center justify-center rounded-lg">
                        <div class="flex flex-col items-center gap-3">
                            <flux:icon.loading class="w-8 h-8 text-primary-600" />
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Refining...') }}</span>
                        </div>
                    </div>
                </div>

                @if($this->error)
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-3 text-red-700 dark:text-red-400 text-sm">
                        {{ $this->error }}
                    </div>
                @endif

                <div class="flex gap-2 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">{{ __('Feedback for AI') }}</label>
                        <flux:input wire:model="feedback" placeholder="{{ __('e.g. Make it shorter, add more upper body...') }}" />
                    </div>
                    <flux:button variant="ghost" wire:click="refine" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('Refine') }}</span>
                        <span wire:loading>{{ __('Refining...') }}</span>
                    </flux:button>
                </div>

                <div class="flex justify-end gap-2">
                    <flux:button variant="ghost" wire:click="resetForNew">{{ __('Start over') }}</flux:button>
                    <flux:button variant="primary" wire:click="accept">{{ __('Accept') }}</flux:button>
                </div>
            </div>
        @endif
    </div>
</flux:modal>
