<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTeleponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (bool) auth()->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     * SEC-03: Format telepon 8-15 digit, awalan opsional +
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'telepon' => [
                'nullable',
                'string',
                'regex:/^(\+?[0-9]{8,15})?$/',
            ],
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'telepon.regex' => 'Format nomor telepon harus berupa 8 hingga 15 digit angka (opsional awalan +).',
        ];
    }
}
