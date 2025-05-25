<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RoomImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url ? asset(Storage::url("uploads/{$this->url}")) : asset("uploads/default_profile.jpeg"),
            'room_id' => $this->room_id,
        ];
    }
}
