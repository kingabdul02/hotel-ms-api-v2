<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelPolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'policy' => $this->policy,
            'policy_type_id' => $this->policy_type_id,
            'hotel_id' => $this->hotel_id,
        ];
    }
}
