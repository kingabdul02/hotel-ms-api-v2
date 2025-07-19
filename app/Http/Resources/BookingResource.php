<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'guest_name' => $this->guest_name,
            'room_id' => $this->room_id,
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'special_requests' => $this->special_requests,
            'total_amount' => $this->total_amount,
            'payment_status' => $this->payment_status,
            'is_confirmed' => $this->is_confirmed,
            'is_checked_in' => $this->is_checked_in,
            'is_checked_out' => $this->is_checked_out,
            'no_of_guests' => $this->no_of_guests,
            'no_of_nights' => $this->no_of_nights,
            'booking_id' => $this->booking_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'room' => new RoomResource($this->room),
            'paymentEntry' => new PaymentEntryResource($this->paymentEntry),
            'cancelationRequest' => CancelationRequestResource::make($this->whenLoaded('cancelationRequest')),
        ];
    }
}
