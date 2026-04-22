<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\AICoachSettings;
use App\Services\CrossfitCoachService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AICoachModal extends Component
{
    use AuthorizesRequests;

    public string $step = 'parameters';

    public string $intensity = '';

    public array $equipment = [];

    public string $focus_area = '';

    public string $difficulty = '';

    public int $duration = 45;

    public string $wodHtml = '';

    public string $feedback = '';

    public bool $loading = false;

    public ?string $error = null;

    public function mount(): void
    {
        $settings = $this->getSettings();
        $this->intensity = $settings->intensity[0] ?? 'medium';
        $this->equipment = $settings->equipment ? array_slice($settings->equipment, 0, 3) : [];
        $this->focus_area = $settings->focus_area[0] ?? 'mixed';
        $this->difficulty = $settings->difficulty[0] ?? 'intermediate';
        $this->duration = $settings->duration_min ?? 45;
    }

    protected function getSettings(): AICoachSettings
    {
        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? session('tenant_id');

        $settings = AICoachSettings::query()
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $settings) {
            $settings = AICoachSettings::create([
                'tenant_id' => $tenantId,
                ...AICoachSettings::defaults(),
            ]);
        }

        return $settings;
    }

    public function getSettingsProperty(): AICoachSettings
    {
        return $this->getSettings();
    }

    public function generate(CrossfitCoachService $coach): void
    {
        $this->validate([
            'intensity' => 'required|string',
            'equipment' => 'required|array|min:1',
            'equipment.*' => 'string',
            'focus_area' => 'required|string',
            'difficulty' => 'required|string',
            'duration' => 'required|integer|min:5|max:90',
        ]);

        $this->loading = true;
        $this->error = null;

        try {
            $parameters = [
                'intensity' => $this->intensity,
                'equipment' => $this->equipment,
                'focus_area' => $this->focus_area,
                'difficulty' => $this->difficulty,
                'duration' => $this->duration,
            ];
            $this->wodHtml = $coach->generateWod($parameters);
            $this->step = 'review';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function refine(CrossfitCoachService $coach): void
    {
        $this->validate([
            'feedback' => 'required|string|max:2000',
        ]);

        $this->loading = true;
        $this->error = null;

        try {
            $parameters = [
                'intensity' => $this->intensity,
                'equipment' => $this->equipment,
                'focus_area' => $this->focus_area,
                'difficulty' => $this->difficulty,
                'duration' => $this->duration,
            ];
            $this->wodHtml = $coach->refineWod($this->wodHtml, $this->feedback, $parameters);
            $this->feedback = '';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function accept(): void
    {
        $this->dispatch('wod-accepted', html: $this->wodHtml);
        $this->dispatch('modal-close', name: 'ai-coach-modal');
    }

    public function resetForNew(): void
    {
        $this->step = 'parameters';
        $this->wodHtml = '';
        $this->feedback = '';
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.components.ai-coach-modal');
    }
}
