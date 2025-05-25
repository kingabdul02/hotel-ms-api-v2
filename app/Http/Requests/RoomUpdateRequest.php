<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomUpdateRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'price' => ['required', 'numeric'],
            'no_of_guests' => ['required', 'integer'],
            'no_of_bedrooms' => ['required', 'integer'],
            'no_of_beds' => ['required', 'integer'],
            'no_of_baths' => ['required', 'integer'],
            'images' => ['array'],
            'images.*.url' => ['required', 'string'],
        ];
    }
}
