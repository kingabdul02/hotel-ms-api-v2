<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'indoor_area' => $this->indoor_area,
            'out_door_area' => $this->out_door_area,
            'dining' => $this->dining,
            'gym' => $this->gym,
            'comment' => $this->comment,
        ];
    }
}
