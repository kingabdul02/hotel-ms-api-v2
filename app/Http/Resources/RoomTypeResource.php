<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RoomTypeResource extends JsonResource
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
            'chart_color_code' => $this->chart_color_code,
            'image_url' => $this->image_url ? asset(Storage::url("uploads/{$this->image_url}")) : asset("uploads/default_profile.jpeg"),
            'hotel_id' => $this->hotel_id,
            'rooms' => RoomCollection::make($this->whenLoaded('rooms')),
        ];
    }
}
