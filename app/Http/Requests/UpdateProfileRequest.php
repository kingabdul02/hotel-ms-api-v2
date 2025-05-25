<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'phone' => ['nullable','string','min:8'],
            'address' => ['nullable','string'],
            'profile_url' => ['nullable','string'],
            'password' => ['nullable','string','min:6', 'confirmed'],
        ];
    }
}
