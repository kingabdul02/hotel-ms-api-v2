<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CorporateBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'coordinator' => new CoordinatorResource($this->whenLoaded('coordinator')),
            'mealPlan' => new MealPlanResource($this->whenLoaded('mealPlan')),
            'guests' => CorporateBookingGuestResource::collection($this->whenLoaded('guests')),
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'total_cost' => $this->total_cost,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
