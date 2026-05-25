<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymClassResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name, // Laravel Translatable vil håndtere dette
            'description' => $this->description,
            'trainer_id' => $this->trainer_id,
            'class_type_id' => $this->class_type_id,
            'max_participants' => $this->max_participants,
            'class_start' => $this->class_start,
            'class_end' => $this->class_end,
            'all_day_event' => $this->all_day_event,
            'featured' => $this->featured,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
