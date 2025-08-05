<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CorporateBookingHallResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'hall_id' => $this->hall_id,
            'hall_name' => $this->hall_name,
            'hall_price' => $this->hall_price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'amount' => $this->amount,
            'hall' => new HallResource($this->whenLoaded('hall')),
        ];
    }
}
