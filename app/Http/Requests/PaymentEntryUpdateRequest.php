<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentEntryUpdateRequest extends FormRequest
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
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'payment_amount' => ['required', 'numeric'],
            'payment_method' => ['required', 'in:online,cash,pos,transfer'],
            'transaction_id' => ['required', 'string', 'unique:payment_entries,transaction_id'],
            'payment_status' => ['required', 'in:pending,successful,failed'],
            'payment_date' => ['required'],
        ];
    }
}
