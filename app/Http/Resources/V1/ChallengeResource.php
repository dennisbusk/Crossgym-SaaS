<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $userPivot = $this->users->where('id', $user->id)->first()?->pivot;

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('name', 'da'),
            'description' => $this->getTranslation('description', 'da'),
            'progress' => $userPivot?->current_value ?? 0,
            'goal' => $this->goal_value,
            'is_community' => $this->type === 'community',
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'is_active' => $this->is_active,
        ];
    }
}
