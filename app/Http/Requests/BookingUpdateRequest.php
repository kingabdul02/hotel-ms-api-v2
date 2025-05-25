<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date'],
            'special_requests' => ['nullable', 'string'],
            'total_amount' => ['required', 'numeric'],
            'payment_status' => ['required', 'in:pending,paid,cancelled'],
            'is_confirmed' => ['required'],
            'is_checked_in' => ['required'],
            'is_checked_out' => ['required'],
            'no_of_guests' => ['required', 'string'],
            'no_of_nights' => ['required', 'string'],
            'booking_id' => ['required', 'string', 'unique:bookings,booking_id'],
        ];
    }
}
