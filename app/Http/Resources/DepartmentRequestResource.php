<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'department_id' => $this->department_id,
            'item_id' => $this->item_id,
            'quantity' => $this->quantity,
            'created_at' => $this->created_at,
            'status' => $this->status,
        ];
    }
}
