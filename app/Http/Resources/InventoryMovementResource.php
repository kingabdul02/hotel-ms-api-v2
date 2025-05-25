<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'item' => new ItemResource($this->whenLoaded('item')),
            'transaction_type' => $this->transaction_type,
            'quantity' => $this->quantity,
            'transaction_date' => $this->transaction_date,
            'remarks' => $this->remarks,
        ];
    }
}
