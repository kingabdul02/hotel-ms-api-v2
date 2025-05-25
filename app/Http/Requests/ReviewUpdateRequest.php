<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewUpdateRequest extends FormRequest
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
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'rating' => ['required', 'string'],
            'indoor_area' => ['nullable', 'string'],
            'out_door_area' => ['nullable', 'string'],
            'dining' => ['nullable', 'string'],
            'gym' => ['nullable', 'string'],
            'comment' => ['required', 'string'],
        ];
    }
}
