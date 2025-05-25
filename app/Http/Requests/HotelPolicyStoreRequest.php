<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelPolicyStoreRequest extends FormRequest
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
            'policy' => ['required', 'string'],
            'policy_type_id' => ['required', 'integer', 'exists:policy_types,id'],
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
        ];
    }
}
