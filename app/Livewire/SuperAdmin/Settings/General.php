<?php

declare(strict_types=1);

namespace App\Livewire\SuperAdmin\Settings;

use App\Models\SystemSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

class General extends Component
{
//    public string $stripe_key = '';
//    public string $stripe_secret = '';
//    public string $stripe_webhook_secret = '';

    public function mount(): void
    {
//        $data = Cache::rememberForever('system_settings.stripe', function () {
//            return optional(SystemSetting::firstWhere('key', 'stripe'))?->value ?? [];
//        });
//
//        $this->stripe_key = (string)($data['key'] ?? '');
//        $this->stripe_secret = (string)($data['secret'] ?? '');
//        $this->stripe_webhook_secret = (string)($data['webhook_secret'] ?? '');
    }

    public function save(): void
    {
//        $validated = $this->validate([
//            'stripe_key' => ['nullable','string'],
//            'stripe_secret' => ['nullable','string'],
//            'stripe_webhook_secret' => ['nullable','string'],
//        ]);
//
//        $payload = [
//            'key' => $validated['stripe_key'] ?? '',
//            'secret' => $validated['stripe_secret'] ?? '',
//            'webhook_secret' => $validated['stripe_webhook_secret'] ?? '',
//        ];

//        SystemSetting::updateOrCreate(['key' => 'stripe'], ['value' => $payload]);
//        Cache::forget('system_settings.stripe');

        session()->flash('status', __('Settings saved.'));
    }

    public function render(): View
    {
        return view('livewire.superadmin.settings.general');
    }
}
