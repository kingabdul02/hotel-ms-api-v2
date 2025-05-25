<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_name' => $this->supplier_name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'purchaseOrders' => PurchaseOrderCollection::make($this->whenLoaded('purchaseOrders')),
        ];
    }
}
