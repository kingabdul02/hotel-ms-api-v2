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
            'reservation_code' => $this->reservation_code,
            'expected_guests' => $this->expected_guests,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'coordinator' => new CoordinatorResource($this->whenLoaded('coordinator')),
            'mealPlan' => new MealPlanResource($this->whenLoaded('mealPlan')),
            'guests' => CorporateBookingGuestResource::collection($this->whenLoaded('guests')),
            'halls' => CorporateBookingHallResource::collection($this->whenLoaded('halls')),
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'total_cost' => $this->total_cost,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
