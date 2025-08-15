<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
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
            'guest_name' => ['nullable', 'string'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'special_requests' => ['nullable', 'string'],
            'no_of_guests' => ['required', 'integer', 'min:1'],
            'is_online_booking' => ['nullable', 'boolean'],
            // Optional discount fields from the frontend payload
            'discount_type' => ['nullable', 'in:percent,amount'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
