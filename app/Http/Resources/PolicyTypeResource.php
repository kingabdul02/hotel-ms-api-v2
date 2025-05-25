<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'hotel_id' => $this->hotel_id,
            'hotelPolicies' => HotelPolicyCollection::make($this->whenLoaded('hotelPolicies')),
        ];
    }
}
