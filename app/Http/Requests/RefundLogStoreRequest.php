<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundLogStoreRequest extends FormRequest
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
            'booking_id' => ['required', 'integer', 'exists:bookings,id', 'unique:refund_logs,booking_id'],
            'booking_amount' => ['required', 'numeric'],
            'refund_amount' => ['required', 'numeric'],
            'fees' => ['required', 'numeric'],
        ];
    }
}
