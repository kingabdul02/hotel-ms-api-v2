<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class MealPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'price_per_day' => $this->price_per_day,
            'description' => $this->description,
        ];
    }
}
