<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomSearchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'room_type_id' => 'nullable|integer',
            'no_of_guests' => 'nullable|integer',
            'no_of_bedrooms' => 'nullable|integer',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
        ];
    }

    public function messages()
    {
        return [
            'check_out_date.after_or_equal' => 'Check-out date must be after or equal to check-in date.',
        ];
    }
}
