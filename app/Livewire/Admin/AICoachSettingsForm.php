<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\AICoachSettings;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AICoachSettingsForm extends Component
{
    use AuthorizesRequests;

    public const AI_PROVIDERS = [
        'gemini' => 'Google Gemini',
        'openai' => 'OpenAI',
        'groq' => 'Groq',
        'anthropic' => 'Anthropic',
        'xai' => 'xAI (Grok)',
        'mistral' => 'Mistral',
        'deepseek' => 'DeepSeek',
        'cohere' => 'Cohere',
        'ollama' => 'Ollama (local)',
    ];

    public AICoachSettings $settings;

    public array $equipment = [];

    public array $intensity = [];

    public array $focus_area = [];

    public array $difficulty = [];

    public int $duration_min = 45;

    public int $duration_max = 60;

    public ?string $ai_provider = null;

    public string $ai_api_key = '';

    public string $newEquipment = '';

    public string $newIntensity = '';

    public string $newFocusArea = '';

    public string $newDifficulty = '';

    public function mount(): void
    {
        $user = Auth::user();
        $tenantId = $user?->tenant_id ?? session('tenant_id');

        $this->settings = AICoachSettings::query()
            ->where('tenant_id', $tenantId)
            ->firstOrCreate(
                ['tenant_id' => $tenantId],
                AICoachSettings::defaults()
            );

        $this->authorize('update', $this->settings);

        $this->equipment = $this->settings->equipment ?? [];
        $this->intensity = $this->settings->intensity ?? [];
        $this->focus_area = $this->settings->focus_area ?? [];
        $this->difficulty = $this->settings->difficulty ?? [];
        $this->duration_min = $this->settings->duration_min ?? 45;
        $this->duration_max = $this->settings->duration_max ?? 60;
        $this->ai_provider = $this->settings->ai_provider;
        $this->ai_api_key = '';
    }

    public function addEquipment(): void
    {
        $value = trim($this->newEquipment);
        if ($value && ! in_array($value, $this->equipment, true)) {
            $this->equipment[] = $value;
            $this->newEquipment = '';
        }
    }

    public function removeEquipment(int $index): void
    {
        unset($this->equipment[$index]);
        $this->equipment = array_values($this->equipment);
    }

    public function addIntensity(): void
    {
        $value = trim($this->newIntensity);
        if ($value && ! in_array($value, $this->intensity, true)) {
            $this->intensity[] = $value;
            $this->newIntensity = '';
        }
    }

    public function removeIntensity(int $index): void
    {
        unset($this->intensity[$index]);
        $this->intensity = array_values($this->intensity);
    }

    public function addFocusArea(): void
    {
        $value = trim($this->newFocusArea);
        if ($value && ! in_array($value, $this->focus_area, true)) {
            $this->focus_area[] = $value;
            $this->newFocusArea = '';
        }
    }

    public function removeFocusArea(int $index): void
    {
        unset($this->focus_area[$index]);
        $this->focus_area = array_values($this->focus_area);
    }

    public function addDifficulty(): void
    {
        $value = trim($this->newDifficulty);
        if ($value && ! in_array($value, $this->difficulty, true)) {
            $this->difficulty[] = $value;
            $this->newDifficulty = '';
        }
    }

    public function removeDifficulty(int $index): void
    {
        unset($this->difficulty[$index]);
        $this->difficulty = array_values($this->difficulty);
    }

    public function save(): void
    {
        $rules = [
            'equipment' => 'required|array|min:1',
            'equipment.*' => 'string',
            'intensity' => 'required|array|min:1',
            'intensity.*' => 'string',
            'focus_area' => 'required|array|min:1',
            'focus_area.*' => 'string',
            'difficulty' => 'required|array|min:1',
            'difficulty.*' => 'string',
            'duration_min' => 'required|integer|min:5|max:90',
            'duration_max' => 'required|integer|min:5|max:90|gte:duration_min',
        ];

        if ($this->ai_provider) {
            $rules['ai_provider'] = 'required|string|in:'.implode(',', array_keys(self::AI_PROVIDERS));
            $rules['ai_api_key'] = 'required|string|min:1';
        }

        $this->validate($rules);

        $data = [
            'equipment' => $this->equipment,
            'intensity' => $this->intensity,
            'focus_area' => $this->focus_area,
            'difficulty' => $this->difficulty,
            'duration_min' => $this->duration_min,
            'duration_max' => $this->duration_max,
            'ai_provider' => $this->ai_provider ?: null,
        ];

        if ($this->ai_api_key !== '') {
            $data['ai_api_key'] = $this->ai_api_key;
        }

        $this->settings->update($data);

        $this->ai_api_key = '';
        session()->flash('status', __('AI Coach settings saved.'));
    }

    public function render()
    {
        return view('livewire.admin.ai-coach-settings-form');
    }
}
