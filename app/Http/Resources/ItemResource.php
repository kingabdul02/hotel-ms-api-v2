<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ItemResource extends JsonResource
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
            'category' => new CategoryResource($this->category),
            'unit_price' => $this->unit_price,
            'reorder_level' => $this->reorder_level,
            'image_url' => $this->image_url ? asset(Storage::url("uploads/{$this->image_url}")) : asset("uploads/default_profile.jpeg"),
            'departmentRequests' => DepartmentRequestCollection::make($this->whenLoaded('departmentRequests')),
            'inventory' => new InventoryResource($this->whenLoaded('inventory')),
            'inventoryMovements' => new InventoryMovementCollection($this->whenLoaded('inventoryMovements')),
        ];
    }
}
