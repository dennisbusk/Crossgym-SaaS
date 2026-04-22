<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateWodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $defaults = config('crossfit.defaults');

        $equipment = $this->input('equipment', $defaults['equipment']);

        if (is_string($equipment)) {
            $equipment = collect(explode(',', $equipment))
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->values()
                ->all();
        }

        if (! is_array($equipment) || empty($equipment)) {
            $equipment = $defaults['equipment'];
        }

        $this->merge([
            'intensity' => $this->input('intensity', $defaults['intensity']),
            'equipment' => $equipment,
            'focus_area' => $this->input('focus_area', $defaults['focus_area']),
            'difficulty' => $this->input('difficulty', $defaults['difficulty']),
            'duration' => (int) $this->input('duration', $defaults['duration']),
        ]);
    }

    public function rules(): array
    {
        return [
            'intensity' => [
                'required',
                'string',
                Rule::in(config('crossfit.allowed.intensity')),
            ],
            'equipment' => ['required', 'array', 'min:1'],
            'equipment.*' => [
                'required',
                'string',
                Rule::in(config('crossfit.allowed.equipment')),
            ],
            'focus_area' => [
                'required',
                'string',
                Rule::in(config('crossfit.allowed.focus_area')),
            ],
            'difficulty' => [
                'required',
                'string',
                Rule::in(config('crossfit.allowed.difficulty')),
            ],
            'duration' => ['required', 'integer', 'min:5', 'max:90'],
        ];
    }
}
