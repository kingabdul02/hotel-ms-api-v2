<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'payment_amount' => $this->payment_amount,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'payment_status' => $this->payment_status,
            'payment_date' => $this->payment_date,
            'portal' => $this->portal,
            'booking_type' => $this->booking_type,
        ];
    }
}
