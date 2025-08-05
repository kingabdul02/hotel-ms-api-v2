<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorporateBookingRequest extends FormRequest
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
            'company_id' => 'required_if:is_new_company,false',
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
            'halls' => 'nullable|array',
            'halls.*.hall_id' => 'required_with:halls|exists:halls,id',
            'halls.*.hall_name' => 'required_with:halls|string',
            'halls.*.hall_price' => 'required_with:halls|numeric|min:0',
            'halls.*.start_date' => 'required_with:halls|date',
            'halls.*.end_date' => 'required_with:halls|date|after:halls.*.start_date',
            'halls.*.amount' => 'required_with:halls|numeric|min:0',
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
