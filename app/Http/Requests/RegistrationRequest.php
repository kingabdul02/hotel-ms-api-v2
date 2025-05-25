<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required','string','min:6', 'confirmed'],
            'name' => ['required','string','max:500'],
            'phone' => ['required','string','min:6'],
            'address' => ['nullable','string','min:10'],
            'role' => ['required', 'in:Guest,Admin,Manager,FrontDesk,InventoryStaff'],
            'profile_picture' => ['nullable','string','max:500'],
        ];
    }
}
