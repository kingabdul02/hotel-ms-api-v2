<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InAppNotificationUpdateRequest extends FormRequest
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
            'subject' => ['required', 'string'],
            'content' => ['required', 'string'],
        ];
    }
}
