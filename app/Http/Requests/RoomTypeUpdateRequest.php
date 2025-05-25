<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomTypeUpdateRequest extends FormRequest
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
            'description' => ['required', 'string'],
            'chart_color_code' => ['required', 'string'],
            // 'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'image_url' => ['nullable', 'string'],
        ];
    }
}
