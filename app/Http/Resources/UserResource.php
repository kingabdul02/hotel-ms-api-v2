<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'rooms_booked_counts' => $this->rooms_booked_counts,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'profile_url' => $this->profile_url ? asset(Storage::url("users/{$this->profile_url}")) : asset("users/default_profile.jpeg"),
            'role' => $this->roles[0]['name'],
            'reviews' => new ReviewCollection($this->whenLoaded('reviews')),
            'bookings' => new BookingCollection($this->whenLoaded('bookings')),
            'notifications' => new InAppNotificationCollection($this->whenLoaded('notifications')),
            'savedBookings' => new SavedBookingCollection($this->whenLoaded('savedBookings')),
        ];
    }
}
