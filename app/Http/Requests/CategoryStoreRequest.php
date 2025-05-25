<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
            'chart_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->missing('chart_color')) {
            $this->merge([
                'chart_color' => $this->generateRandomColor(),
            ]);
        }
    }

    /**
     * Generate a random color in hexadecimal format.
     *
     * @return string
     */
    private function generateRandomColor(): string
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
