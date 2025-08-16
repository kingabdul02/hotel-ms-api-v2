<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOutletRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'operating_hours' => 'nullable|array',
        ];
    }
}
