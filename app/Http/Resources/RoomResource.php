<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'hotel_id' => $this->hotel_id,
            'price' => $this->price,
            'is_available' => $this->is_available,
            'no_of_guests' => $this->no_of_guests,
            'no_of_bedrooms' => $this->no_of_bedrooms,
            'has_sitting_room' => $this->has_sitting_room,
            'no_of_beds' => $this->no_of_beds,
            'no_of_baths' => $this->no_of_baths,
            'is_feature' => $this->is_feature,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'roomType' => new RoomTypeResource($this->roomType),
            'facilities' => FacilityCollection::make($this->whenLoaded('facilities')),
            'images' => RoomImageCollection::make($this->images),
        ];
    }
}
