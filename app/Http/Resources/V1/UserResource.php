<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => new RoleResource($this->whenLoaded('role')),
            'tenant_id' => $this->tenant_id,
            'medlemsnummer' => $this->medlemsnummer,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'birthday' => $this->birthday,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'sex' => $this->sex,
            'joined_at' => $this->joined_at,
            'left_at' => $this->left_at,
            'is_approved_for_closed_classes' => $this->is_approved_for_closed_classes,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
