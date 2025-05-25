<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryMovementStoreRequest extends FormRequest
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
            'item_id' => 'required|exists:items,id',
            'transaction_type' => 'required|in:IN,OUT',
            'quantity' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'remarks' => 'required|string',
        ];
    }
}
