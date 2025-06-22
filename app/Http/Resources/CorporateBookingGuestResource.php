<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CorporateBookingGuestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'corporate_booking_id' => $this->corporate_booking_id,
            'room' => new RoomResource($this->room),
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_checked_in' => $this->is_checked_in,
            'checked_in_at' => $this->checked_in_at,
            'is_checked_out' => $this->is_checked_out,
            'checked_out_at' => $this->checked_out_at,
        ];
    }
}
