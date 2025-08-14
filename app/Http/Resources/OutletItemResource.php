<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutletItemResource extends JsonResource
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
            'price' => (float) $this->price,
            'description' => $this->description,
            'available' => (bool) $this->available,
            'tax_rate' => (float) $this->tax_rate,
            'image_url' => $this->image_url,
            'sku' => $this->sku,
            'category_id' => $this->category_id,
            'outlet_id' => $this->outlet_id,
        ];
    }
}
