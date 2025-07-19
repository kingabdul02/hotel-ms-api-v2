<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorporateBookingUpdateRequest extends FormRequest
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
        $rules = [
            'is_new_company' => 'boolean',
            'coordinator.full_name' => 'required|string',
            'coordinator.email' => 'required|email',
            'coordinator.phone' => 'required|string',
            'coordinator.nin' => 'required|digits:11',
            'coordinator.id_card_file' => 'nullable|string',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'meal_plan_id' => 'nullable|exists:meal_plans,id',
            'expected_guests' => 'nullable',
            'guests' => 'required|array|min:1',
            'guests.*.full_name' => 'required|string',
            'guests.*.gender' => 'required|string|in:Male,Female',
            'guests.*.email' => 'nullable|string',
            'guests.*.phone' => 'nullable|string',
            'guests.*.room_id' => 'required|exists:rooms,id',
        ];

        // Only require company details if it's a new company
        if ($this->input('is_new_company')) {
            $rules = array_merge($rules, [
                'company.name' => 'required|string|unique:companies,name',
                'company.email' => 'required|email|unique:companies,email',
                'company.phone' => 'required|string|unique:companies,phone',
                'company.address' => 'required|string',
            ]);
        }

        return $rules;
    }
}
