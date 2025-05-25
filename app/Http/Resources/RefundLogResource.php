<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_amount' => $this->booking_amount,
            'refund_amount' => $this->refund_amount,
            'fees' => $this->fees,
            'booking' => new BookingResource($this->booking),
        ];
    }
}
