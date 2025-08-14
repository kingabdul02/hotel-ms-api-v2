<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class GetPOSItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
            'category' => ['nullable', 'string'],
        ];
    }
}
